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
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;
use Stg\HallOfRecords\Shared\Infrastructure\Type\DateTime;

/**
 * @phpstan-import-type Names from CompanyRecord
 */
final class CompaniesTable
{
    private Connection $connection;
    private IdGenerator $idGenerator;
    private Locales $locales;

    public function __construct(
        Connection $connection,
        Locales $locales
    ) {
        $this->connection = $connection;
        $this->locales = $locales;
        $this->idGenerator = new IdGenerator();
    }

    public function createObjects(
        AbstractSchemaManager $schemaManager,
        Schema $schema
    ): Table {
        $companies = $schema->createTable('stg_companies');
        $companies->addColumn('id', 'integer');
        $companies->addColumn('created_date', 'datetime');
        $companies->addColumn('last_modified_date', 'datetime');
        $companies->setPrimaryKey(['id']);
        $schemaManager->createTable($companies);

        $localeCompanies = $schema->createTable('stg_companies_locale');
        $localeCompanies->addColumn('company_id', 'integer');
        $localeCompanies->addColumn('locale', 'string', ['length' => 16]);
        $localeCompanies->addColumn('name', 'string', ['length' => 100]);
        $localeCompanies->setPrimaryKey(['company_id', 'locale']);
        $localeCompanies->addForeignKeyConstraint($companies, ['company_id'], ['id']);
        $schemaManager->createTable($localeCompanies);

        $schemaManager->createView($this->createView());

        return $companies;
    }

    private function createView(): View
    {
        $qb = $this->connection->createQueryBuilder();

        return new View('stg_query_companies', $qb->select(
            'companies.id',
            'companies.created_date',
            'companies.last_modified_date',
            'localized.locale',
            'localized.name'
        )
            ->from('stg_companies_locale', 'localized')
            ->join(
                'localized',
                'stg_companies',
                'companies',
                $qb->expr()->eq('companies.id', 'localized.company_id')
            )
            ->getSQL()
        );
    }

    /**
     * @param Names $names
     */
    public function createRecord(
        ?int $companyId,
        array $names
    ): CompanyRecord {
        return new CompanyRecord(
            $companyId ?? $this->idGenerator->nextId(),
            $this->localizeValues($names)
        );
    }

    public function insertRecord(CompanyRecord $record): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('stg_companies')
            ->values([
                'id' => ':id',
                'created_date' => ':createdDate',
                'last_modified_date' => ':lastModifiedDate',
            ])
            ->setParameter('id', $record->id())
            ->setParameter('createdDate', DateTime::now())
            ->setParameter('lastModifiedDate', DateTime::now())
            ->executeStatement();

        foreach ($this->locales->all() as $locale) {
            $this->insertLocalizedRecord($record, $locale);
        }
    }

    /**
     * @param CompanyRecord[] $records
     */
    public function insertRecords(array $records): void
    {
        foreach ($records as $record) {
            $this->insertRecord($record);
        }
    }

    private function insertLocalizedRecord(
        CompanyRecord $record,
        string $locale
    ): void {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('stg_companies_locale')
            ->values([
                'company_id' => ':companyId',
                'locale' => ':locale',
                'name' => ':name',
            ])
            ->setParameter('companyId', $record->id())
            ->setParameter('locale', $locale)
            ->setParameter('name', $record->name($locale))
            ->executeStatement();
    }

    /**
     * @template T
     * @param array<string,T> $values
     * @return array<string,T>
     */
    private function localizeValues(array $values): array
    {
        $localized = [];

        foreach ($this->locales->all() as $locale) {
            $localized[$locale] = $values[$locale];
        }

        return $localized;
    }
}
