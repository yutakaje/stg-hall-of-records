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

namespace Tests\HallOfRecords\Game;

use Fig\Http\Message\StatusCodeInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Tests\Helper\Data\GameEntry;

class ListGamesTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        // Index represents expected sort order.
        $games = [
            1 => $this->data()->createGame(
                $this->data()->createCompany('CAVE'),
                'Akai Katana',
                'akai katana'
            ),
            0 => $this->data()->createGame(
                $this->data()->createCompany('Tanoshimasu'),
                'Aka to Blue Type-R',
                'aka to blue type-r'
            ),
            3 => $this->data()->createGame(
                $this->data()->createCompany('Visco'),
                'Asuka & Asuka',
                'asuka & asuka'
            ),
            2 => $this->data()->createGame(
                $this->data()->createCompany('SNK'),
                'ASO: Armored Scrum Object / Alpha Mission',
                'aso: armored scrum object / alpha mission'
            ),
        ];

        foreach ($games as $game) {
            $this->addScores($game);
        }

        $this->testWithLocale($games, $this->locale()->get('en'));
    }

    public function testWithJaLocale(): void
    {
        // Index represents expected sort order.
        $games = [
            1 => $this->data()->createGame(
                $this->data()->createCompany('ケイブ'),
                'ケツイ〜絆地獄たち〜',
                'けついきずなじごくたち'
            ),
            0 => $this->data()->createGame(
                $this->data()->createCompany('ケイブ'),
                'エスプレイド',
                'えすぷれいど'
            ),
            3 => $this->data()->createGame(
                $this->data()->createCompany('ライジング / エイティング'),
                'バトルガレッガ',
                'ばとるがれっが'
            ),
            2 => $this->data()->createGame(
                $this->data()->createCompany('コナミ'),
                '出たな!ツインビー',
                'でたな!ついんびー',
            ),
        ];

        foreach ($games as $game) {
            $this->addScores($game);
        }

        $this->testWithLocale($games, $this->locale()->get('ja'));
    }

    /**
     * @param GameEntry[] $games
     */
    private function testWithLocale(
        array $games,
        Locale $locale
    ): void {
        $this->insertGames($games);

        $request = $this->http()->createServerRequest(
            'GET',
            "/{$locale}/games"
        );

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->mediaWiki()->canonicalizeHtml(
                $this->createOutput($games, $locale)
            ),
            $this->mediaWiki()->canonicalizeHtml(
                (string)$response->getBody()
            )
        );
    }

    private function addScores(GameEntry $game): void
    {
        // Adding scores ensures that the count functions work as expected.
        // The actual sore properties are not important here.
        $numScores = random_int(1, 5);
        for ($i = 0; $i < $numScores; ++$i) {
            $game->addScore(
                $this->data()->createScore(
                    $game,
                    $this->data()->createPlayer("Player{$i}"),
                    "player{$i}",
                    (string)random_int(1000, 99999)
                )
            );
        }
    }

    /**
     * @param GameEntry[] $games
     */
    private function insertGames(array $games): void
    {
        foreach ($games as $game) {
            $this->data()->insertGame($game);
            $this->data()->insertScores($game->scores());
        }
    }

    /**
     * @param GameEntry[] $games
     */
    private function createOutput(array $games, Locale $locale): string
    {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadBasicTemplate(
                    $this->createGamesOutput($games, $locale),
                    $locale,
                    '/{locale}/games'
                )
            )
        );
    }

    /**
     * @param GameEntry[] $games
     */
    private function createGamesOutput(array $games, Locale $locale): string
    {
        ksort($games);

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Game', 'list-games/main'),
            [
                '{{ entry|raw }}' => implode(PHP_EOL, array_map(
                    fn (GameEntry $game) => $this->createGameOutput(
                        $game,
                        $locale
                    ),
                    $games
                )),
            ]
        );
    }

    private function createGameOutput(
        GameEntry $game,
        Locale $locale
    ): string {
        $numScores = sizeof($game->scores());

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Game', 'list-games/game-entry'),
            [
                '{{ game.name }}' => htmlentities($game->name($locale)),
                '{{ game.numScores }}' => $numScores,
                "{'%count%': game.numScores}" => "{'%count%': {$numScores}}",
                '{{ links.game }}' => "/{$locale}/games/{$game->id()}",
            ]
        );
    }
}
