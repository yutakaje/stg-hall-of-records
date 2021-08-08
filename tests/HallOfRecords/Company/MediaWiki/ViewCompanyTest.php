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
use Tests\Helper\Data\CompanyEntry;
use Tests\Helper\Data\GameEntry;

class ViewCompanyTest extends \Tests\TestCase
{
    public function testWithDefaultLocale(): void
    {
        $request = $this->http()->createServerRequest('GET', '/companies/{id}');

        $this->testWithLocale($request, $this->locale()->default());
    }

    public function testWithRandomLocale(): void
    {
        $locale = $this->locale()->random();

        $request = $this->http()->createServerRequest('GET', '/companies/{id}')
            ->withHeader('Accept-Language', $locale);

        $this->testWithLocale($request, $locale);
    }

    private function testWithLocale(
        ServerRequestInterface $request,
        string $locale
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
                $this->data()->createGame("game{$i}", $company)
            );
        }

        return $company;
    }

    private function insertCompany(CompanyEntry $company): void
    {
        $this->data()->insertCompany($company);
        $this->data()->insertGames($company->games());
    }

    private function createOutput(CompanyEntry $company, string $locale): string
    {
        return str_replace(
            '{{content|raw}}',
            $this->createCompanyOutput($company, $locale),
            $this->mediaWiki()->loadTemplate('Shared', 'basic')
        );
    }

    private function createCompanyOutput(
        CompanyEntry $company,
        string $locale
    ): string {
        return str_replace(
            [
                '{{ company.link }}',
                '{{ company.name }}',
                '{{ games|raw }}',
            ],
            [
                "/companies/{$company->id()}",
                $company->name($locale),
                $this->createGamesOutput($company->games(), $locale),
            ],
            $this->mediaWiki()->loadTemplate('Company', 'view-company/main')
        );
    }

    /**
     * @param GameEntry[] $games
     */
    private function createGamesOutput(array $games, string $locale): string
    {
        return $this->mediaWiki()->removePlaceholders(str_replace(
            [
                "{{ games|length }}",
                "{{ entry|raw }}",
            ],
            [
                sizeof($games),
                implode(PHP_EOL, array_map(
                    fn (GameEntry $game) => $this->createGameOutput($game, $locale),
                    $games
                )),
            ],
            $this->mediaWiki()->loadTemplate('Company', 'view-company/games-list')
        ));
    }

    private function createGameOutput(GameEntry $game, string $locale): string
    {
        return str_replace(
            [
                '{{ game.link }}',
                '{{ game.name }}',
            ],
            [
                "/games/{$game->id()}",
                $game->name($locale),
            ],
            $this->mediaWiki()->loadTemplate('Company', 'view-company/game-entry')
        );
    }
}
