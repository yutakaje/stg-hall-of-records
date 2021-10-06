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
use Tests\Helper\Data\ScoreEntries;

class ViewGameTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        $game = $this->data()->createGame(
            $this->data()->createCompany('CAVE'),
            'Akai Katana'
        );
        $game->setScores(new ScoreEntries([
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('player2'),
                'player2',
                '992,893,110'
            ),
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('player3'),
                'player3',
                '503,434,050'
            ),
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('player1'),
                'player1',
                '234,828,910'
            ),
            $this->data()->createScore(
                $game,
                null,
                '[unknown]',
                '234,828,910'
            ),
        ]));

        $this->insertGame($game);

        $this->executeTest(
            'view-game.output.en',
            $this->locale()->get('en'),
            $game->id()
        );
    }

    public function testWithJaLocale(): void
    {
        $game = $this->data()->createGame(
            $this->data()->createCompany('ケイブ'),
            'エスプレイド'
        );
        $game->setScores(new ScoreEntries([
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('プレイヤー2'),
                'プレイヤー2',
                '992,893,110'
            ),
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('プレイヤー3'),
                'プレイヤー3',
                '503,434,050'
            ),
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('プレイヤー1'),
                'プレイヤー1',
                '234,828,910'
            ),
        ]));

        $this->insertGame($game);

        $this->executeTest(
            'view-game.output.ja',
            $this->locale()->get('ja'),
            $game->id()
        );
    }

    private function executeTest(
        string $expectedOutputFile,
        Locale $locale,
        int $gameId
    ): void {
        $request = $this->http()->createServerRequest(
            'GET',
            "/{$locale}/games/{$gameId}"
        );

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->createOutput(
                $this->filesystem()->loadFile(
                    __DIR__ . "/view-game/{$expectedOutputFile}"
                ),
                $locale,
                $gameId
            ),
            (string)$response->getBody()
        );
    }

    private function createOutput(
        string $content,
        Locale $locale,
        int $gameId
    ): string {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadMainTemplate(
                    $content,
                    $locale,
                    "/{locale}/games/{$gameId}",
                )
            )
        );
    }

    private function insertGame(GameEntry $game): void
    {
        $this->data()->insertGame($game);
        $this->data()->insertScores($game->scores()->entries());
    }
}
