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

namespace Tests\HallOfRecords\Data\Score;

use Stg\HallOfRecords\Data\Score\Score;
use Stg\HallOfRecords\Data\Score\ScoreRepository;
use Stg\HallOfRecords\Data\Score\Scores;

class ScoreRepositoryTest extends \Tests\TestCase
{
    public function testFilterByGameWithNoScoresAtAll(): void
    {
        $gameId = $this->randomGameId();

        $repository = new ScoreRepository();

        self::assertEquals(new Scores(), $repository->filterByGame($gameId));
    }

    public function testFilterByGameWithNoScores(): void
    {
        $gameId = $this->randomGameId();
        $scores = $this->createScores($gameId + 1);

        $repository = new ScoreRepository();
        $scores->apply($this->addToRepository($repository));

        self::assertEquals(new Scores(), $repository->filterByGame($gameId));
    }

    public function testFilterByGame(): void
    {
        $gameId = $this->randomGameId();
        $scores = $this->createScores($gameId);

        $repository = new ScoreRepository();
        $scores->apply($this->addToRepository($repository));

        self::assertEquals(
            new Scores(array_slice($scores->asArray(), 2)),
            $repository->filterByGame($gameId)
        );
    }

    private function createScores(int $gameId): Scores
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
                'gameId' => $gameId,
                'player' => 'SPS',
                'score' => '507,780,433',
                'ship' => 'Type A',
                'mode' => 'Omote',
                'scoredDate' => '2014-08',
                'source' => 'Arcadia August 2014',
                'comments' => [],
            ]),
            $this->createScore([
                'gameId' => $gameId,
                'player' => 'Akuma',
                'score' => '614,129,975',
                'ship' => 'Type A',
                'mode' => 'Ura',
                'scoredDate' => '2021-01',
                'source' => 'Arcadia January 2021',
                'comments' => [],
            ]),
            $this->createScore([
                'gameId' => $gameId,
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
                'gameId' => $gameId,
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

    private function addToRepository(ScoreRepository $repository): \Closure
    {
        return function (Score $score) use ($repository): void {
            $repository->add($score);
        };
    }

    private function randomGameId(): int
    {
        return random_int(1000, 9999);
    }
}
