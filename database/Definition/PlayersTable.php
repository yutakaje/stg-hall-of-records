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
use Nette\Utils\Json;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;
use Stg\HallOfRecords\Shared\Infrastructure\Type\DateTime;

/**
 * @phpstan-import-type Aliases from PlayerRecord
 */
final class PlayersTable extends AbstractTable
{
    private Connection $connection;

    public function __construct(
        Connection $connection,
        Locales $locales
    ) {
        parent::__construct($locales);
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
        $players->addColumn('aliases', 'string', ['length' => 500]);
        $players->setPrimaryKey(['id']);
        $schemaManager->createTable($players);

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
                'aliases' => ':aliases',
            ])
            ->setParameter('createdDate', DateTime::now())
            ->setParameter('lastModifiedDate', DateTime::now())
            ->setParameter('name', $record->name())
            ->setParameter('aliases', Json::encode(
                array_values($record->aliases())
            ))
            ->executeStatement();

        $record->setId((int)$this->connection->lastInsertId());
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
}
