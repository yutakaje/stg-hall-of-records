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

use Stg\HallOfRecords\Data\Game\Game;
use Stg\HallOfRecords\Data\Game\Games;
use Stg\HallOfRecords\Data\Score\Score;
use Stg\HallOfRecords\Data\Score\Scores;
use Stg\HallOfRecords\Data\Setting\GameSetting;
use Stg\HallOfRecords\Data\Setting\GlobalSetting;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var \Generator<int> */
    private \Generator $gameIdGenerator;
    /** @var \Generator<int> */
    private \Generator $scoreIdGenerator;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->gameIdGenerator = $this->createIdGenerator();
        $this->scoreIdGenerator = $this->createIdGenerator();
    }

    /**
     * @param \Generator<int> $generator
     */
    protected function nextId(\Generator $generator): int
    {
        $value = $generator->current();
        $generator->next();
        return $value;
    }

    /**
     * @return \Generator<int> $generator
     */
    protected function createIdGenerator(): \Generator
    {
        $id = 1;
        while (true) {
            yield $id++;
        }
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createGlobalSetting(array $properties): GlobalSetting
    {
        return new GlobalSetting(
            $properties['name'] ?? '',
            $properties['value'] ?? null
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createGameSetting(array $properties): GameSetting
    {
        return new GameSetting(
            $properties['gameId'] ?? 0,
            $properties['name'] ?? '',
            $properties['value'] ?? null
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createGame(array $properties): Game
    {
        return new Game(
            $properties['id'] ?? $this->nextId($this->gameIdGenerator),
            $properties['name'] ?? '',
            $properties['company'] ?? ''
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createScore(array $properties): Score
    {
        return new Score(
            $properties['id'] ?? $this->nextId($this->scoreIdGenerator),
            $properties['gameId'] ?? $this->nextId($this->gameIdGenerator),
            $properties['player'] ?? '',
            $properties['score'] ?? '',
            $properties['ship'] ?? '',
            $properties['mode'] ?? '',
            $properties['weapon'] ?? '',
            $properties['scored-date'] ?? '',
            $properties['source'] ?? '',
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
