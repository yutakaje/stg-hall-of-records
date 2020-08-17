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

use Doctrine\DBAL\Connection;
use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\Games;
use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\Scores;
use Stg\HallOfRecords\Database\ConnectionFactory;
use Stg\HallOfRecords\Database\InMemoryDatabaseCreator;

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

    protected function prepareDatabase(): Connection
    {
        $databaseCreator = new InMemoryDatabaseCreator(
            new ConnectionFactory()
        );
        return $databaseCreator->create();
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

    protected function insertGames(Connection $connection, Games $games): void
    {
        $qb = $connection->createQueryBuilder();

        foreach ($games->asArray() as $game) {
            $qb->insert('games')
                ->values([
                    'id' => ':id',
                    'name' => ':name',
                    'company' => ':company',
                ])
                ->setParameter(':id', $game->id())
                ->setParameter(':name', $game->name())
                ->setParameter(':company', $game->company())
                ->execute();
        }
    }

    protected function insertScores(Connection $connection, Scores $scores): void
    {
        $qb = $connection->createQueryBuilder();

        foreach ($scores->asArray() as $score) {
            $qb->insert('scores')
                ->values([
                    'id' => ':id',
                    'game_id' => ':gameId',
                    'player' => ':player',
                    'score' => ':score',
                    'ship' => ':ship',
                    'mode' => ':mode',
                    'weapon' => ':weapon',
                    'scored_date' => ':scoredDate',
                    'source' => ':source',
                    'comments' => ':comments',
                ])
                ->setParameter(':id', $score->id())
                ->setParameter(':gameId', $score->gameId())
                ->setParameter(':player', $score->player())
                ->setParameter(':score', $score->score())
                ->setParameter(':ship', $score->ship())
                ->setParameter(':mode', $score->mode())
                ->setParameter(':weapon', $score->weapon())
                ->setParameter(':scoredDate', $score->scoredDate())
                ->setParameter(':source', $score->source())
                ->setParameter(':comments', json_encode($score->comments()))
                ->execute();
        }
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
