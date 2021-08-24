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
use Tests\Helper\Data\GameEntries;
use Tests\Helper\Data\GameEntry;

class ViewCompanyTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        $company = $this->data()->createCompany('Capcom', 'capcom');
        $company->setGames(new GameEntries([
            $this->data()->createGame(
                $company,
                'Aka to Blue Type-R',
                'aka to blue type-r'
            ),
            $this->data()->createGame(
                $company,
                'Akai Katana',
                'akai katana'
            ),
            $this->data()->createGame(
                $company,
                'ASO: Armored Scrum Object / Alpha Mission',
                'aso: armored scrum object / alpha mission'
            ),
            $this->data()->createGame(
                $company,
                'Asuka & Asuka',
                'asuka & asuka'
            ),
        ]));

        $this->testWithLocale($company, $this->locale()->get('en'));
    }

    public function testWithJaLocale(): void
    {
        $company = $this->data()->createCompany('彩京', 'さいきょう');
        $company->setGames(new GameEntries([
            $this->data()->createGame(
                $company,
                'エスプレイド',
                'えすぷれいど'
            ),
            $this->data()->createGame(
                $company,
                'ケツイ〜絆地獄たち〜',
                'けついきずなじごくたち'
            ),
            $this->data()->createGame(
                $company,
                '出たな!ツインビー',
                'でたな!ついんびー',
            ),
            $this->data()->createGame(
                $company,
                'バトルガレッガ',
                'ばとるがれっが'
            ),
        ]));

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

    private function insertCompany(CompanyEntry $company): void
    {
        $this->data()->insertCompany($company);
        $this->data()->insertGames($company->games()->entries());
    }

    private function createOutput(CompanyEntry $company, Locale $locale): string
    {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadBasicTemplate(
                    $this->createCompanyOutput($company, $locale),
                    $locale,
                    "/{locale}/companies/{$company->id()}"
                )
            )
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
                '{{ games|raw }}' => $this->createGamesOutput(
                    $company->games(),
                    $locale
                ),
                '{{ links.company }}' => "/{$locale}/companies/{$company->id()}",
            ]
        );
    }

    private function createGamesOutput(
        GameEntries $games,
        Locale $locale
    ): string {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Company', 'view-company/games-list'),
            [
                "{{ games|length }}" => $games->numEntries(),
                "{{ entry|raw }}" => implode(PHP_EOL, array_map(
                    fn (GameEntry $game) => $this->createGameOutput(
                        $game,
                        $locale
                    ),
                    $games->sorted()
                )),
            ]
        );
    }

    private function createGameOutput(GameEntry $game, Locale $locale): string
    {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Company', 'view-company/game-entry'),
            [
                '{{ game.name }}' => htmlentities($game->name($locale)),
                '{{ links.game }}' => "/{$locale}/games/{$game->id()}",
            ]
        );
    }
}
