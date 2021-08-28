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
use Tests\Helper\Data\GameEntries;
use Tests\Helper\Data\GameEntry;
use Tests\Helper\Data\ScoreEntries;

class ListGamesTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        $games = new GameEntries([
            $this->data()->createGame(
                $this->data()->createCompany('Tanoshimasu'),
                'Aka to Blue Type-R',
                'aka to blue type-r'
            ),
            $this->data()->createGame(
                $this->data()->createCompany('CAVE'),
                'Akai Katana',
                'akai katana'
            ),
            $this->data()->createGame(
                $this->data()->createCompany('SNK'),
                'ASO: Armored Scrum Object / Alpha Mission',
                'aso: armored scrum object / alpha mission'
            ),
            $this->data()->createGame(
                $this->data()->createCompany('Visco'),
                'Asuka & Asuka',
                'asuka & asuka'
            ),
        ]);

        foreach ($games->entries() as $game) {
            $this->addScores($game);
        }

        $this->testWithLocale($games, $this->locale()->get('en'));
    }

    public function testWithJaLocale(): void
    {
        $games = new GameEntries([
            $this->data()->createGame(
                $this->data()->createCompany('ケイブ'),
                'エスプレイド',
                'えすぷれいど'
            ),
            $this->data()->createGame(
                $this->data()->createCompany('ケイブ'),
                'ケツイ〜絆地獄たち〜',
                'けついきずなじごくたち'
            ),
            $this->data()->createGame(
                $this->data()->createCompany('コナミ'),
                '出たな!ツインビー',
                'でたな!ついんびー',
            ),
            $this->data()->createGame(
                $this->data()->createCompany('ライジング / エイティング'),
                'バトルガレッガ',
                'ばとるがれっが'
            ),
        ]);

        foreach ($games->entries() as $game) {
            $this->addScores($game);
        }

        $this->testWithLocale($games, $this->locale()->get('ja'));
    }

    private function testWithLocale(
        GameEntries $games,
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
        // The actual score properties are not important here.
        $scores = array_map(
            fn (int $i) => $this->data()->createScore(
                $game,
                $this->data()->createPlayer("player{$i}"),
                "player{$i}",
                (string)random_int(1000, 99999)
            ),
            range(1, random_int(1, 5))
        );

        $game->setScores(new ScoreEntries($scores));
    }

    private function insertGames(GameEntries $games): void
    {
        foreach ($games->entries() as $game) {
            $this->data()->insertGame($game);
            $this->data()->insertScores($game->scores()->entries());
        }
    }

    private function createOutput(
        GameEntries $games,
        Locale $locale
    ): string {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadMainTemplate(
                    $this->createGamesOutput($games, $locale),
                    $locale,
                    '/{locale}/games'
                )
            )
        );
    }

    private function createGamesOutput(
        GameEntries $games,
        Locale $locale
    ): string {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Game', 'list-games/main'),
            [
                '{{ entry|raw }}' => implode(PHP_EOL, array_map(
                    fn (GameEntry $game) => $this->createGameOutput(
                        $game,
                        $locale
                    ),
                    $games->sorted()
                )),
            ]
        );
    }

    private function createGameOutput(
        GameEntry $game,
        Locale $locale
    ): string {
        $numScores = $game->scores()->numEntries();

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
