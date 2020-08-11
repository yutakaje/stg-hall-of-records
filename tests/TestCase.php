<?php

declare(strict_types=1);

/*
 * This file is part of the stg/hall-of-records package.
 *
 * (c) YTK <yutakaje@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests;

use Doctrine\DBAL\Connection;
use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\GameFactory;
use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\ScoreFactory;
use Stg\HallOfRecords\Database\ConnectionFactory;
use Stg\HallOfRecords\Database\InMemoryDatabaseCreator;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private GameFactory $gameFactory;
    private ScoreFactory $scoreFactory;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->gameFactory = new GameFactory();
        $this->scoreFactory = new ScoreFactory();
    }

    protected function prepareDatabase(): Connection
    {
        $connection = (new ConnectionFactory())->create();
        $dbCreator = new InMemoryDatabaseCreator($connection);
        $dbCreator->create();
        return $connection;
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createGame(array $properties): Game
    {
        return $this->gameFactory->create(
            $properties['id'] ?? $this->gameFactory->nextId(),
            $properties['name'],
            $properties['company'],
            $properties['scores']
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createScore(array $properties): Score
    {
        return $this->scoreFactory->create(
            $properties['id'] ?? $this->scoreFactory->nextId(),
            $properties['player'],
            $properties['score'],
            $properties['ship'],
            $properties['mode'],
            $properties['weapon'] ?? '',
            $properties['scored-date'],
            $properties['source'],
            $properties['comments'] ?? []
        );
    }
}
