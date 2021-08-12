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

/**
 * @phpstan-import-type Aliases from PlayerRecord
 */
final class PlayersTable
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function createObjects(
        AbstractSchemaManager $schemaManager,
        Schema $schema
    ): Table {
        $players = $schema->createTable('stg_players');
        $players->addColumn('id', 'integer', ['autoincrement' => true]);
        $players->addColumn('created_date', 'datetime');
        $players->addColumn('last_modified_date', 'datetime');
        $players->addColumn('name', 'string', ['length' => 100]);
        $players->setPrimaryKey(['id']);
        $schemaManager->createTable($players);

        $playerAliases = $schema->createTable('stg_player_aliases');
        $playerAliases->addColumn('id', 'integer', ['autoincrement' => true]);
        $playerAliases->addColumn('player_id', 'integer');
        $playerAliases->addColumn('alias', 'string', ['length' => 100]);
        $playerAliases->setPrimaryKey(['id']);
        $playerAliases->addForeignKeyConstraint($players, ['player_id'], ['id']);
        $schemaManager->createTable($playerAliases);

        return $players;
    }

    /**
     * @param Aliases $aliases
     */
    public function createRecord(
        string $name,
        array $aliases = []
    ): PlayerRecord {
        return new PlayerRecord(
            $name,
            $aliases
        );
    }

    public function insertRecord(PlayerRecord $record): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('stg_players')
            ->values([
                'created_date' => ':createdDate',
                'last_modified_date' => ':lastModifiedDate',
                'name' => ':name',
            ])
            ->setParameter('createdDate', DateTime::now())
            ->setParameter('lastModifiedDate', DateTime::now())
            ->setParameter('name', $record->name())
            ->executeStatement();

        $record->setId((int)$this->connection->lastInsertId());

        foreach ($record->aliases() as $alias) {
            $this->insertAliasRecord($record, $alias);
        }
    }

    /**
     * @param PlayerRecord[] $records
     */
    public function insertRecords(array $records): void
    {
        foreach ($records as $record) {
            $this->insertRecord($record);
        }
    }

    private function insertAliasRecord(
        PlayerRecord $record,
        string $alias
    ): void {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('stg_player_aliases')
            ->values([
                'player_id' => ':playerId',
                'alias' => ':alias',
            ])
            ->setParameter('playerId', $record->id())
            ->setParameter('alias', $alias)
            ->executeStatement();
    }
}
