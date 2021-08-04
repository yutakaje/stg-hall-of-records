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
use Stg\HallOfRecords\Database\IdGenerator;
use Stg\HallOfRecords\Shared\Infrastructure\Type\DateTime;

/**
 * @phpstan-import-type Aliases from PlayerRecord
 */
final class PlayersTable
{
    private Connection $connection;
    private IdGenerator $playerIdGenerator;
    private IdGenerator $aliasIdGenerator;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->playerIdGenerator = new IdGenerator();
        $this->aliasIdGenerator = new IdGenerator();
    }

    public function createObjects(
        AbstractSchemaManager $schemaManager,
        Schema $schema
    ): Table {
        $players = $schema->createTable('stg_players');
        $players->addColumn('id', 'integer');
        $players->addColumn('created_date', 'datetime');
        $players->addColumn('last_modified_date', 'datetime');
        $players->addColumn('name', 'string', ['length' => 100]);
        $players->setPrimaryKey(['id']);
        $schemaManager->createTable($players);

        $localePlayers = $schema->createTable('stg_player_aliases');
        $localePlayers->addColumn('id', 'integer');
        $localePlayers->addColumn('player_id', 'integer');
        $localePlayers->addColumn('alias', 'string', ['length' => 100]);
        $localePlayers->setPrimaryKey(['id']);
        $localePlayers->addForeignKeyConstraint($players, ['player_id'], ['id']);
        $schemaManager->createTable($localePlayers);

        return $players;
    }

    /**
     * @param Aliases $aliases
     */
    public function createRecord(
        ?int $playerId,
        string $name,
        array $aliases = []
    ): PlayerRecord {
        return new PlayerRecord(
            $playerId ?? $this->playerIdGenerator->nextId(),
            $name,
            $aliases
        );
    }

    public function insertRecord(PlayerRecord $record): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('stg_players')
            ->values([
                'id' => ':id',
                'created_date' => ':createdDate',
                'last_modified_date' => ':lastModifiedDate',
                'name' => ':name',
            ])
            ->setParameter('id', $record->id())
            ->setParameter('createdDate', DateTime::now())
            ->setParameter('lastModifiedDate', DateTime::now())
            ->setParameter('name', $record->name())
            ->executeStatement();

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
                'id' => ':id',
                'player_id' => ':playerId',
                'alias' => ':alias',
            ])
            ->setParameter('id', $this->aliasIdGenerator->nextId())
            ->setParameter('playerId', $record->id())
            ->setParameter('alias', $alias)
            ->executeStatement();
    }
}
