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
 * @phpstan-import-type LocalizedValues from CompanyRecord
 */
final class CompaniesTable extends AbstractTable
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
        $companies = $schema->createTable('stg_companies');
        $companies->addColumn('id', 'integer', ['autoincrement' => true]);
        $companies->addColumn('created_date', 'datetime');
        $companies->addColumn('last_modified_date', 'datetime');
        $companies->addColumn('name_filter', 'string', ['length' => 500]);
        $companies->setPrimaryKey(['id']);
        $schemaManager->createTable($companies);

        $localeCompanies = $schema->createTable('stg_companies_locale');
        $localeCompanies->addColumn('company_id', 'integer');
        $localeCompanies->addColumn('locale', 'string', ['length' => 16]);
        $localeCompanies->addColumn('name', 'string', ['length' => 100]);
        $localeCompanies->addColumn('name_translit', 'string', ['length' => 100]);
        $localeCompanies->setPrimaryKey(['company_id', 'locale']);
        $localeCompanies->addForeignKeyConstraint($companies, ['company_id'], ['id']);
        $schemaManager->createTable($localeCompanies);

        $schemaManager->createView($this->createView());

        return $companies;
    }

    private function createView(): View
    {
        $qb = $this->connection->createQueryBuilder();
        $alias = 'x';

        return new View('stg_query_companies', $qb->select(
            'id',
            'created_date',
            'last_modified_date',
            'locale',
            'name',
            'name_translit',
            'name_filter'
        )
            ->from("({$this->companySql()})", $alias)
            ->getSQL());
    }

    private function companySql(): string
    {
        $qb = $this->connection->createQueryBuilder();

        return $qb->select(
            'companies.id',
            'companies.created_date',
            'companies.last_modified_date',
            'localized.locale',
            'localized.name',
            'localized.name_translit',
            'companies.name_filter'
        )
            ->from('stg_companies_locale', 'localized')
            ->join(
                'localized',
                'stg_companies',
                'companies',
                $qb->expr()->eq('companies.id', 'localized.company_id')
            )
            ->getSQL();
    }

    /**
     * @param LocalizedValues $names
     * @param LocalizedValues $translitNames
     */
    public function createRecord(
        array $names,
        array $translitNames = []
    ): CompanyRecord {
        if ($translitNames == null) {
            $translitNames = $names;
        }

        return new CompanyRecord(
            $this->localizeValues($names),
            $this->localizeValues($translitNames)
        );
    }

    public function insertRecord(CompanyRecord $record): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('stg_companies')
            ->values([
                'created_date' => ':createdDate',
                'last_modified_date' => ':lastModifiedDate',
                'name_filter' => ':nameFilter',
            ])
            ->setParameter('createdDate', DateTime::now())
            ->setParameter('lastModifiedDate', DateTime::now())
            ->setParameter('nameFilter', $this->makeNameFilter($record))
            ->executeStatement();

        $record->setId((int)$this->connection->lastInsertId());

        foreach ($this->locales()->all() as $locale) {
            $this->insertLocalizedRecord($record, $locale);
        }
    }

    /**
     * @param CompanyRecord[] $records
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

    private function insertLocalizedRecord(
        CompanyRecord $record,
        Locale $locale
    ): void {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('stg_companies_locale')
            ->values([
                'company_id' => ':companyId',
                'locale' => ':locale',
                'name' => ':name',
                'name_translit' => ':translitName',
            ])
            ->setParameter('companyId', $record->id())
            ->setParameter('locale', $locale->value())
            ->setParameter('name', $record->name($locale))
            ->setParameter('translitName', $record->translitName($locale))
            ->executeStatement();
    }

    private function makeNameFilter(CompanyRecord $record): string
    {
        return implode('|', array_reduce(
            $this->locales()->all(),
            fn (array $entries, Locale $locale) => array_merge($entries, [
                $record->name($locale),
                $record->translitName($locale),
            ]),
            []
        ));
    }
}
