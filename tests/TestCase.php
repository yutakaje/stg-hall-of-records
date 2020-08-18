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

namespace Tests;

use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\Games;
use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\Scores;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private int $nextGameId;
    private int $nextScoreId;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->nextGameId = 1;
        $this->nextScoreId = 1;
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createGame(array $properties): Game
    {
        return new Game(
            $properties['id'] ?? $this->nextGameId++,
            $properties['name'],
            $properties['company']
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createScore(array $properties): Score
    {
        return new Score(
            $properties['id'] ?? $this->nextScoreId++,
            $properties['gameId'] ?? $this->nextGameId++,
            $properties['player'],
            $properties['score'],
            $properties['ship'],
            $properties['mode'],
            $properties['weapon'] ?? '',
            $properties['scoredDate'],
            $properties['source'],
            $properties['comments'] ?? []
        );
    }

    protected function loadFile(string $filename): string
    {
        $contents = file_get_contents($filename);

        if ($contents === false) {
            throw new \UnexpectedValueException(
                "Unable to load file: `{$filename}`"
            );
        }

        return $contents;
    }
}
