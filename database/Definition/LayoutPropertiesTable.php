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
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;
use Stg\HallOfRecords\Shared\Infrastructure\Type\DateTime;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-import-type Value from LayoutPropertyRecord
 */
final class LayoutPropertiesTable extends AbstractTable
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
        Schema $schema,
        Table $games
    ): Table {
        $layouts = $schema->createTable('stg_layout_properties');
        $layouts->addColumn('id', 'integer', ['autoincrement' => true]);
        $layouts->addColumn('created_date', 'datetime');
        $layouts->addColumn('last_modified_date', 'datetime');
        $layouts->addColumn('game_id', 'integer', ['notnull' => false]);
        $layouts->addColumn('name', 'string', ['length' => 32]);
        $layouts->addColumn('locale', 'string', ['length' => 16, 'notnull' => false]);
        $layouts->addColumn('value', 'string', ['length' => 100]);
        $layouts->setPrimaryKey(['id']);
        $layouts->addForeignKeyConstraint($games, ['game_id'], ['id']);
        $schemaManager->createTable($layouts);

        $schemaManager->createView($this->createView());

        return $layouts;
    }

    private function createView(): View
    {
        $qb = $this->connection->createQueryBuilder();
        $alias = 'x';

        return new View('stg_query_layout_properties', $qb->select(
            'id',
            'created_date',
            'last_modified_date',
            'game_id',
            'name',
            'locale',
            'value'
        )
            ->from('stg_layout_properties', $alias)
            ->getSQL());
    }

    /**
     * @param Value $value
     */
    public function createRecord(
        ?int $gameId,
        string $name,
        $value,
        ?Locale $locale = null
    ): LayoutPropertyRecord {
        return new LayoutPropertyRecord(
            $gameId,
            $name,
            $value,
            $locale
        );
    }

    public function insertRecord(LayoutPropertyRecord $record): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('stg_layout_properties')
            ->values([
                'created_date' => ':createdDate',
                'last_modified_date' => ':lastModifiedDate',
                'game_id' => ':gameId',
                'name' => ':name',
                'locale' => ':locale',
                'value' => ':value',
            ])
            ->setParameter('createdDate', DateTime::now())
            ->setParameter('lastModifiedDate', DateTime::now())
            ->setParameter('gameId', $record->gameId())
            ->setParameter('name', $record->name())
            ->setParameter('locale', $record->locale())
            ->setParameter('value', $this->makeValue($record))
            ->executeStatement();

        $record->setId((int)$this->connection->lastInsertId());
    }

    /**
     * @param LayoutPropertyRecord[] $records
     */
    public function insertRecords(array $records): void
    {
        $this->connection->transactional(
            function () use ($records): void {
                foreach ($records as $record) {
                    $this->insertRecord($record);
                }
            }
        );
    }

    private function makeValue(LayoutPropertyRecord $record): string
    {
        return Yaml::dump(
            $record->value()
        );
    }
}
