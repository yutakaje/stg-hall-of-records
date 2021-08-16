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
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Tests\Helper\Data\CompanyEntry;
use Tests\Helper\Data\GameEntry;

class ViewCompanyTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        $company = $this->data()->createCompany('Capcom', 'capcom');

        $this->addGames($company, [
            $this->data()->createGame($company, "game"),
        ]);

        $this->testWithLocale($company, $this->locale()->get('en'));
    }

    public function testWithJaLocale(): void
    {
        $company = $this->data()->createCompany('彩京', 'さいきょう');

        $this->addGames($company, [
            $this->data()->createGame($company, "game"),
        ]);

        $this->testWithLocale($company, $this->locale()->get('ja'));
    }

    private function testWithLocale(CompanyEntry $company, Locale $locale): void
    {
        $this->insertCompany($company);

        $request = $this->http()->createServerRequest(
            'GET',
            "/{$locale}/companies/{$company->id()}"
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

    /**
     * @param GameEntry[] $games
     */
    private function addGames(CompanyEntry $company, array $games): void
    {
        // Adding games ensures that the games are displayed as expected.
        foreach ($games as $game) {
            $company->addGame($game);
        }
    }

    private function insertCompany(CompanyEntry $company): void
    {
        $this->data()->insertCompany($company);
        $this->data()->insertGames($company->games());
    }

    private function createOutput(CompanyEntry $company, Locale $locale): string
    {
        return $this->mediaWiki()->loadBasicTemplate(
            $this->createCompanyOutput($company, $locale),
            $locale
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
                '{{ links.company }}' => "/{$locale}/companies/{$company->id()}",
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
                '{{ links.game }}' => "/{$locale}/games/{$game->id()}",
            ]
        );
    }
}
