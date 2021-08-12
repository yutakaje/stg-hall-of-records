<?php

/*
 * This file is part of the stg/hall-of-records package.
 *
 * (c) YTK <yutakaje@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\HallOfRecords\Company\MediaWiki;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Tests\Helper\Data\CompanyEntry;
use Tests\Helper\Data\GameEntry;

class ViewCompanyTest extends \Tests\TestCase
{
    public function testWithDefaultLocale(): void
    {
        $locale = $this->locale()->default();

        $request = $this->http()->createServerRequest('GET', "/{$locale->value()}/companies/{id}");

        $this->testWithLocale($request, $locale);
    }

    public function testWithRandomLocale(): void
    {
        $locale = $this->locale()->random();

        $request = $this->http()->createServerRequest('GET', "/{$locale->value()}/companies/{id}")
            ->withHeader('Accept-Language', $locale->value());

        $this->testWithLocale($request, $locale);
    }

    private function testWithLocale(
        ServerRequestInterface $request,
        Locale $locale
    ): void {
        $company = $this->createCompany();

        $this->insertCompany($company);

        $request = $this->http()->replaceInUriPath(
            $request,
            '{id}',
            (string)$company->id()
        );

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->mediaWiki()->canonicalizeHtml(
                $this->createOutput($company, $locale)
            ),
            $this->mediaWiki()->canonicalizeHtml(
                (string)$response->getBody()
            )
        );
    }

    private function createCompany(): CompanyEntry
    {
        $company = $this->data()->createCompany('konami');

        // Add some games for this company to ensure
        // that the games are displayed as expected.
        $numGames = random_int(1, 5);
        for ($i = 0; $i < $numGames; ++$i) {
            $company->addGame(
                $this->data()->createGame($company, "game{$i}")
            );
        }

        return $company;
    }

    private function insertCompany(CompanyEntry $company): void
    {
        $this->data()->insertCompany($company);
        $this->data()->insertGames($company->games());
    }

    private function createOutput(CompanyEntry $company, Locale $locale): string
    {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Shared', 'basic'),
            [
                '{{content|raw}}' => $this->createCompanyOutput($company, $locale),
            ]
        );
    }

    private function createCompanyOutput(
        CompanyEntry $company,
        Locale $locale
    ): string {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Company', 'view-company/main'),
            [
                '{{ company.name }}' => $company->name($locale),
                '{{ games|raw }}' => $this->createGamesOutput($company->games(), $locale),
                '{{ links.company }}' => "/{$locale->value()}/companies/{$company->id()}",
            ]
        );
    }

    /**
     * @param GameEntry[] $games
     */
    private function createGamesOutput(array $games, Locale $locale): string
    {
        return $this->mediaWiki()->removePlaceholders(
            $this->data()->replace(
                $this->mediaWiki()->loadTemplate('Company', 'view-company/games-list'),
                [
                    "{{ games|length }}" => sizeof($games),
                    "{{ entry|raw }}" => implode(PHP_EOL, array_map(
                        fn (GameEntry $game) => $this->createGameOutput(
                            $game,
                            $locale
                        ),
                        $games
                    )),
                ]
            )
        );
    }

    private function createGameOutput(GameEntry $game, Locale $locale): string
    {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Company', 'view-company/game-entry'),
            [
                '{{ game.name }}' => $game->name($locale),
                '{{ links.game }}' => "/{$locale->value()}/games/{$game->id()}",
            ]
        );
    }
}
