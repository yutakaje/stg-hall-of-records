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

/**
 * @phpstan-import-type LocalizedSources from ScoreRecord
 * @phpstan-import-type LocalizedValues from ScoreAttributeRecord
 */
final class ScoreAttributesTable extends AbstractTable
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
        Table $scores
    ): Table {
        $attributes = $schema->createTable('stg_score_attrs');
        $attributes->addColumn('id', 'integer', ['autoincrement' => true]);
        $attributes->addColumn('created_date', 'datetime');
        $attributes->addColumn('last_modified_date', 'datetime');
        $attributes->addColumn('score_id', 'integer');
        $attributes->addColumn('name', 'string', ['length' => 32]);
        $attributes->addColumn('value', 'string', ['length' => 100]);
        $attributes->setPrimaryKey(['id']);
        $attributes->addForeignKeyConstraint($scores, ['score_id'], ['id']);
        $schemaManager->createTable($attributes);

        $localeAttributes = $schema->createTable('stg_score_attrs_locale');
        $localeAttributes->addColumn('attr_id', 'integer');
        $localeAttributes->addColumn('locale', 'string', ['length' => 16]);
        $localeAttributes->addColumn('title', 'string', ['length' => 100]);
        $localeAttributes->setPrimaryKey(['attr_id', 'locale']);
        $localeAttributes->addForeignKeyConstraint($attributes, ['attr_id'], ['id']);
        $schemaManager->createTable($localeAttributes);

        $schemaManager->createView($this->createView());

        return $scores;
    }

    private function createView(): View
    {
        $qb = $this->connection->createQueryBuilder();
        $alias = 'x';

        return new View('stg_query_score_attrs', $qb->select(
            'id',
            'created_date',
            'last_modified_date',
            'score_id',
            'locale',
            'name',
            'value',
            'title'
        )
            ->from("({$this->attributeSql()})", $alias)
            ->getSQL());
    }

    private function attributeSql(): string
    {
        $qb = $this->connection->createQueryBuilder();

        return $qb->select(
            'attrs.id',
            'attrs.created_date',
            'attrs.last_modified_date',
            'localized.locale',
            'attrs.score_id',
            'attrs.name',
            'attrs.value',
            'localized.title'
        )
            ->from('stg_score_attrs', 'attrs')
            ->join(
                'attrs',
                'stg_score_attrs_locale',
                'localized',
                $qb->expr()->eq('localized.attr_id', 'attrs.id')
            )
            ->getSQL();
    }

    /**
     * @param LocalizedValues $titles
     */
    public function createRecord(
        string $name,
        string $value,
        array $titles = []
    ): ScoreAttributeRecord {
        if ($titles == null) {
            $titles = $this->emptyLocalizedValues('');
        }

        return new ScoreAttributeRecord(
            $name,
            $value,
            $this->localizeValues($titles)
        );
    }

    public function insertRecord(ScoreAttributeRecord $record): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('stg_score_attrs')
            ->values([
                'created_date' => ':createdDate',
                'last_modified_date' => ':lastModifiedDate',
                'score_id' => ':scoreId',
                'name' => ':name',
                'value' => ':value',
            ])
            ->setParameter('createdDate', DateTime::now())
            ->setParameter('lastModifiedDate', DateTime::now())
            ->setParameter('scoreId', $record->scoreId())
            ->setParameter('name', $record->name())
            ->setParameter('value', $record->value())
            ->executeStatement();

        $record->setId((int)$this->connection->lastInsertId());

        foreach ($this->locales()->all() as $locale) {
            $this->insertLocalizedRecord($record, $locale);
        }
    }

    /**
     * @param ScoreAttributeRecord[] $records
     */
    public function insertRecords(array $records): void
    {
        foreach ($records as $record) {
            $this->insertRecord($record);
        }
    }

    private function insertLocalizedRecord(
        ScoreAttributeRecord $record,
        Locale $locale
    ): void {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('stg_score_attrs_locale')
            ->values([
                'attr_id' => ':attrId',
                'locale' => ':locale',
                'title' => ':title',
            ])
            ->setParameter('attrId', $record->id())
            ->setParameter('locale', $locale->value())
            ->setParameter('title', $record->title($locale))
            ->executeStatement();
    }
}
