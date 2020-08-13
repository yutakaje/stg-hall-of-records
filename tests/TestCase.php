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
use Stg\HallOfRecords\Data\GameFactory;
use Stg\HallOfRecords\Data\Games;
use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\ScoreFactory;
use Stg\HallOfRecords\Data\Scores;
use Stg\HallOfRecords\Database\ConnectionFactory;
use Stg\HallOfRecords\Database\InMemoryDatabaseCreator;
use Stg\HallOfRecords\Import\ParsedDataFactory;
use Stg\HallOfRecords\Import\ParsedGame;
use Stg\HallOfRecords\Import\ParsedGlobalProperties;
use Stg\HallOfRecords\Import\ParsedScore;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private ParsedDataFactory $parsedDataFactory;
    private GameFactory $gameFactory;
    private ScoreFactory $scoreFactory;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->parsedDataFactory = new ParsedDataFactory();
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
    protected function createParsedGlobalProperties(
        array $properties
    ): ParsedGlobalProperties {
        return $this->parsedDataFactory->createGlobalProperties(
            $properties['description'] ?? ''
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createParsedGame(array $properties): ParsedGame
    {
        return $this->parsedDataFactory->createGame(
            $properties['name'],
            $properties['company'],
            $properties['scores']
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createParsedScore(array $properties): ParsedScore
    {
        return $this->parsedDataFactory->createScore(
            $properties['player'],
            $properties['score'],
            $properties['ship'] ?? '',
            $properties['mode'] ?? '',
            $properties['weapon'] ?? '',
            $properties['scoredDate'] ?? '',
            $properties['source'] ?? '',
            $properties['comments'] ?? []
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createGame(array $properties): Game
    {
        return $this->gameFactory->create(
            $properties['id'] ?? $this->gameFactory->nextId(),
            $properties['name'],
            $properties['company']
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createScore(array $properties): Score
    {
        return $this->scoreFactory->create(
            $properties['id'] ?? $this->scoreFactory->nextId(),
            $properties['gameId'] ?? $this->gameFactory->nextId(),
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
}
