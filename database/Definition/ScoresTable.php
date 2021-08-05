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

namespace Stg\HallOfRecords\Database\Definition;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\View;
use Stg\HallOfRecords\Shared\Infrastructure\Type\DateTime;

final class ScoresTable
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function createObjects(
        AbstractSchemaManager $schemaManager,
        Schema $schema,
        Table $games,
        Table $players
    ): Table {
        $scores = $schema->createTable('stg_scores');
        $scores->addColumn('id', 'integer', ['autoincrement' => true]);
        $scores->addColumn('created_date', 'datetime');
        $scores->addColumn('last_modified_date', 'datetime');
        $scores->addColumn('game_id', 'integer');
        $scores->addColumn('player_id', 'integer');
        $scores->addColumn('player_name', 'string', ['length' => 100]);
        $scores->addColumn('score_value', 'string', ['length' => 100]);
        $scores->setPrimaryKey(['id']);
        $scores->addForeignKeyConstraint($games, ['game_id'], ['id']);
        $scores->addForeignKeyConstraint($players, ['player_id'], ['id']);
        $schemaManager->createTable($scores);

        $schemaManager->createView($this->createView());

        return $scores;
    }

    private function createView(): View
    {
        $qb = $this->connection->createQueryBuilder();

        return new View('stg_query_scores', $qb->select(
            'scores.id',
            'scores.created_date',
            'scores.last_modified_date',
            'scores.game_id',
            'games.locale',
            'games.name',
            'games.company_id',
            'games.company_name',
            'scores.player_id',
            'scores.player_name',
            'scores.score_value'
        )
            ->from('stg_scores', 'scores')
            ->join(
                'scores',
                'stg_query_games',
                'games',
                $qb->expr()->eq('games.id', 'scores.game_id')
            )
            ->getSQL());
    }

    public function createRecord(
        int $gameId,
        int $playerId,
        string $playerName,
        string $scoreValue
    ): ScoreRecord {
        return new ScoreRecord(
            $gameId,
            $playerId,
            $playerName,
            $scoreValue
        );
    }

    public function insertRecord(ScoreRecord $record): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('stg_scores')
            ->values([
                'created_date' => ':createdDate',
                'last_modified_date' => ':lastModifiedDate',
                'game_id' => ':gameId',
                'player_id' => ':playerId',
                'player_name' => ':playerName',
                'score_value' => ':scoreValue',
            ])
            ->setParameter('createdDate', DateTime::now())
            ->setParameter('lastModifiedDate', DateTime::now())
            ->setParameter('gameId', $record->gameId())
            ->setParameter('playerId', $record->playerId())
            ->setParameter('playerName', $record->playerName())
            ->setParameter('scoreValue', $record->scoreValue())
            ->executeStatement();

        $record->setId((int)$this->connection->lastInsertId());
    }

    /**
     * @param ScoreRecord[] $records
     */
    public function insertRecords(array $records): void
    {
        foreach ($records as $record) {
            $this->insertRecord($record);
        }
    }
}
