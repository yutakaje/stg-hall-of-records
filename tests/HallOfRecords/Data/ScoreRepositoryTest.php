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

namespace Tests\HallOfRecords\Data;

use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\ScoreRepository;
use Stg\HallOfRecords\Data\Scores;

class ScoreRepositoryTest extends \Tests\TestCase
{
    public function testFilterByGameWithNoScoresAtAll(): void
    {
        $game = $this->createGame([
            'name' => 'some game',
            'company' => 'some company',
        ]);

        $repository = new ScoreRepository();

        self::assertEquals(new Scores([]), $repository->filterByGame($game));
    }

    public function testFilterByGameWithNoScores(): void
    {
        $game = $this->createGame([
            'name' => 'some game',
            'company' => 'some company',
        ]);

        $scores = $this->createScores($this->createGame([
            'name' => 'Ketsui: Kizuna Jigoku Tachi',
            'company' => 'Cave',
        ]));

        $repository = new ScoreRepository();
        foreach ($scores->asArray() as $score) {
            $repository->add($score);
        }

        self::assertEquals(new Scores([]), $repository->filterByGame($game));
    }

    public function testFilterByGameWithDefaultSort(): void
    {
        $game = $this->createGame([
            'name' => 'Ketsui: Kizuna Jigoku Tachi',
            'company' => 'Cave',
        ]);
        $scores = $this->createScores($game);

        $repository = new ScoreRepository();
        foreach ($scores->asArray() as $score) {
            $repository->add($score);
        }

        self::assertEquals(
            $this->sortScores($scores, [2, 3, 4, 5]),
            $repository->filterByGame($game)
        );
    }

    public function testFilterByGameSortByScoreDesc(): void
    {
        $game = $this->createGame([
            'name' => 'Ketsui: Kizuna Jigoku Tachi',
            'company' => 'Cave',
        ]);
        $scores = $this->createScores($game);

        $repository = new ScoreRepository();
        foreach ($scores->asArray() as $score) {
            $repository->add($score);
        }

        self::assertEquals(
            $this->sortScores($scores, [5, 3, 4, 2]),
            $repository->filterByGame($game, [
                'score' => 'desc',
            ])
        );
    }

    public function testFilterByGameSortByShipAscScoreDesc(): void
    {
        $game = $this->createGame([
            'name' => 'Ketsui: Kizuna Jigoku Tachi',
            'company' => 'Cave',
        ]);
        $scores = $this->createScores($game);

        $repository = new ScoreRepository();
        foreach ($scores->asArray() as $score) {
            $repository->add($score);
        }

        self::assertEquals(
            $this->sortScores($scores, [3, 2, 5, 4]),
            $repository->filterByGame($game, [
                'ship' => 'asc',
                'score' => 'desc',
            ])
        );
    }

    public function testFilterByGameSortByCustomOrder(): void
    {
        $game = $this->createGame([
            'name' => 'Ketsui: Kizuna Jigoku Tachi',
            'company' => 'Cave',
        ]);
        $scores = $this->createScores($game);

        $repository = new ScoreRepository();
        foreach ($scores->asArray() as $score) {
            $repository->add($score);
        }

        self::assertEquals(
            $this->sortScores($scores, [4, 2, 5, 3]),
            $repository->filterByGame($game, [
                'player' => [
                    'GAN',
                    'SPS',
                    'Akuma',
                ],
                'score' => 'desc',
            ])
        );
    }

    public function testFilterByGameWithInvalidSort(): void
    {
        $game = $this->createGame([
            'name' => 'Ketsui: Kizuna Jigoku Tachi',
            'company' => 'Cave',
        ]);
        $scores = $this->createScores($game);

        $repository = new ScoreRepository();
        foreach ($scores->asArray() as $score) {
            $repository->add($score);
        }

        // Invalid columns should be ignored.
        self::assertEquals(
            $this->sortScores($scores, [2, 3, 4, 5]),
            $repository->filterByGame($game, [
                'bad_column' => 'asc',
            ])
        );
    }

    private function createScores(Game $game): Scores
    {
        return new Scores([
            $this->createScore([
                'player' => 'ABI',
                'score' => '530,358,660',
                'ship' => 'Palm',
                'mode' => 'Original',
                'weapon' => 'Normal',
                'scoredDate' => '2008-01',
                'source' => 'Arcadia January 2008',
            ]),
            $this->createScore([
                'player' => 'ISO / Niboshi',
                'score' => '518,902,716',
                'ship' => 'Palm',
                'mode' => 'Original',
                'weapon' => 'Abnormal',
                'scoredDate' => '2007',
                'source' => 'Superplay DVD',
            ]),
            $this->createScore([
                'gameId' => $game->id(),
                'player' => 'SPS',
                'score' => '507,780,433',
                'ship' => 'Type A',
                'mode' => 'Omote',
                'scoredDate' => '2014-08',
                'source' => 'Arcadia August 2014',
                'comments' => [],
            ]),
            $this->createScore([
                'gameId' => $game->id(),
                'player' => 'Akuma',
                'score' => '614,129,975',
                'ship' => 'Type A',
                'mode' => 'Ura',
                'scoredDate' => '2021-01',
                'source' => 'Arcadia January 2021',
                'comments' => [],
            ]),
            $this->createScore([
                'gameId' => $game->id(),
                'player' => 'GAN',
                'score' => '569,741,232',
                'ship' => 'Type B',
                'mode' => 'Ura',
                'scoredDate' => '2016-03',
                'source' => 'JHA March 2016',
                'comments' => [
                    '残6機',
                    '1周 2.85億',
                ],
            ]),
            $this->createScore([
                'gameId' => $game->id(),
                'player' => 'Akuma',
                'score' => '619,873,102',
                'ship' => 'Type B',
                'mode' => 'Ura',
                'scoredDate' => '2021-06',
                'source' => 'Arcadia June 2021',
                'comments' => [],
            ]),
        ]);
    }

    /**
     * @param int[] $order
     */
    private function sortScores(Scores $scores, array $order): Scores
    {
        $unsorted = $scores->asArray();

        return new Scores(array_map(
            fn (int $index) => $unsorted[$index],
            $order
        ));
    }
}
