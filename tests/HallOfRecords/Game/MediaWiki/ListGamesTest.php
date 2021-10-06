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
        $locale = $this->locale()->get('en');

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
            $this->addScores($game, strlen($game->name($locale)));
        }

        $this->insertGames($games);

        $this->executeTest('list-games.output.en', $locale);
    }

    public function testWithJaLocale(): void
    {
        $locale = $this->locale()->get('ja');

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
            $this->addScores($game, strlen($game->name($locale)));
        }

        $this->insertGames($games);

        $this->executeTest('list-games.output.ja', $locale);
    }

    public function testFiltering(): void
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

        foreach ($games->entries() as $i => $game) {
            $this->addScores($game, $i + 1);
        }

        $this->insertGames($games);

        $this->executeTest(
            'list-games.output.en.filtered',
            $this->locale()->get('en'),
            'name like aka'
        );
        $this->executeTest(
            'list-games.output.ja.filtered',
            $this->locale()->get('ja'),
            'name like れ'
        );
    }

    private function executeTest(
        string $expectedOutputFile,
        Locale $locale,
        string $filterValue = ''
    ): void {
        $request = $this->http()->createServerRequest(
            'GET',
            "/{$locale}/games"
        );

        if ($filterValue !== '') {
            $request = $request->withQueryParams([
                'q' => $filterValue,
            ]);
        }

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->createOutput(
                $this->filesystem()->loadFile(
                    __DIR__ . "/list-games/{$expectedOutputFile}"
                ),
                $locale
            ),
            (string)$response->getBody()
        );
    }

    private function createOutput(string $content, Locale $locale): string
    {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadMainTemplate(
                    $content,
                    $locale,
                    '/{locale}/games'
                )
            )
        );
    }

    private function addScores(GameEntry $game, int $numScores = 3): void
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
            range(1, $numScores)
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
}
