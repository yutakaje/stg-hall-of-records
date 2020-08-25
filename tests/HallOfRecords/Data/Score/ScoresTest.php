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

use Stg\HallOfRecords\Data\Score\Scores;

class ScoresTest extends \Tests\TestCase
{
    public function testSortWithEmptyArray(): void
    {
        $scores = $this->createScores();

        self::assertEquals(
            $this->sortScores($scores, [0, 1, 2, 3, 4, 5]),
            $scores->sort([])
        );
    }

    public function testSortByScoreDesc(): void
    {
        $scores = $this->createScores();

        self::assertEquals(
            $this->sortScores($scores, [5, 3, 4, 0, 1, 2]),
            $scores->sort([
                'score' => 'desc',
            ])
        );
    }

    public function testSortByShipAscScoreDesc(): void
    {
        $scores = $this->createScores();

        self::assertEquals(
            $this->sortScores($scores, [0, 1, 3, 2, 5, 4]),
            $scores->sort([
                'ship' => 'asc',
                'score' => 'desc',
            ])
        );
    }

    public function testSortByCustomOrder(): void
    {
        $scores = $this->createScores();

        self::assertEquals(
            $this->sortScores($scores, [4, 2, 5, 3, 0, 1]),
            $scores->sort([
                'player' => [
                    'GAN',
                    'SPS',
                    'Akuma',
                ],
                'score' => 'desc',
            ])
        );
    }

    public function testSortWithInvalidProperty(): void
    {
        $scores = $this->createScores();

        // Invalid properties should be ignored.
        self::assertEquals(
            $this->sortScores($scores, [0, 1, 2, 3, 4, 5]),
            $scores->sort([
                'bad-property' => 'asc',
            ])
        );
    }

    private function createScores(): Scores
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
                'player' => 'SPS',
                'score' => '507,780,433',
                'ship' => 'Type A',
                'mode' => 'Omote',
                'scoredDate' => '2014-08',
                'source' => 'Arcadia August 2014',
                'comments' => [],
            ]),
            $this->createScore([
                'player' => 'Akuma',
                'score' => '614,129,975',
                'ship' => 'Type A',
                'mode' => 'Ura',
                'scoredDate' => '2021-01',
                'source' => 'Arcadia January 2021',
                'comments' => [],
            ]),
            $this->createScore([
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
