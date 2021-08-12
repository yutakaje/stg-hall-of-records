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
 * @phpstan-import-type Names from GameRecord
 */
final class GamesTable
{
    private Connection $connection;
    private Locales $locales;

    public function __construct(
        Connection $connection,
        Locales $locales
    ) {
        $this->connection = $connection;
        $this->locales = $locales;
    }

    public function createObjects(
        AbstractSchemaManager $schemaManager,
        Schema $schema,
        Table $companies
    ): Table {
        $games = $schema->createTable('stg_games');
        $games->addColumn('id', 'integer', ['autoincrement' => true]);
        $games->addColumn('created_date', 'datetime');
        $games->addColumn('last_modified_date', 'datetime');
        $games->addColumn('company_id', 'integer');
        $games->setPrimaryKey(['id']);
        $games->addForeignKeyConstraint($companies, ['company_id'], ['id']);
        $schemaManager->createTable($games);

        $localeGames = $schema->createTable('stg_games_locale');
        $localeGames->addColumn('game_id', 'integer');
        $localeGames->addColumn('locale', 'string', ['length' => 16]);
        $localeGames->addColumn('name', 'string', ['length' => 100]);
        $localeGames->addColumn('name_translit', 'string', ['length' => 100]);
        $localeGames->setPrimaryKey(['game_id', 'locale']);
        $localeGames->addForeignKeyConstraint($games, ['game_id'], ['id']);
        $schemaManager->createTable($localeGames);

        $schemaManager->createView($this->createView());

        return $games;
    }

    private function createView(): View
    {
        $qb = $this->connection->createQueryBuilder();

        return new View('stg_query_games', $qb->select(
            'games.id',
            'games.created_date',
            'games.last_modified_date',
            'localized.locale',
            'localized.name',
            'localized.name_translit',
            'games.company_id',
            'companies.name AS company_name',
            'companies.name_translit AS company_name_translit'
        )
            ->from('stg_games_locale', 'localized')
            ->join(
                'localized',
                'stg_games',
                'games',
                $qb->expr()->eq('games.id', 'localized.game_id')
            )
            ->join(
                'games',
                'stg_query_companies',
                'companies',
                (string)$qb->expr()->and(
                    $qb->expr()->eq('companies.id', 'games.company_id'),
                    $qb->expr()->eq('companies.locale', 'localized.locale')
                )
            )
            ->getSQL());
    }

    /**
     * @param Names $names
     * @param Names $translitNames
     */
    public function createRecord(
        int $companyId,
        array $names,
        array $translitNames = []
    ): GameRecord {
        if ($translitNames == null) {
            $translitNames = $names;
        }

        return new GameRecord(
            $companyId,
            $this->localizeValues($names),
            $this->localizeValues($translitNames)
        );
    }

    public function insertRecord(GameRecord $record): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('stg_games')
            ->values([
                'created_date' => ':createdDate',
                'last_modified_date' => ':lastModifiedDate',
                'company_id' => ':companyId',
            ])
            ->setParameter('createdDate', DateTime::now())
            ->setParameter('lastModifiedDate', DateTime::now())
            ->setParameter('companyId', $record->companyId())
            ->executeStatement();

        $record->setId((int)$this->connection->lastInsertId());

        foreach ($this->locales->all() as $locale) {
            $this->insertLocalizedRecord($record, $locale);
        }
    }

    /**
     * @param GameRecord[] $records
     */
    public function insertRecords(array $records): void
    {
        foreach ($records as $record) {
            $this->insertRecord($record);
        }
    }

    private function insertLocalizedRecord(
        GameRecord $record,
        Locale $locale
    ): void {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('stg_games_locale')
            ->values([
                'game_id' => ':gameId',
                'locale' => ':locale',
                'name' => ':name',
                'name_translit' => ':translitName',
            ])
            ->setParameter('gameId', $record->id())
            ->setParameter('locale', $locale->value())
            ->setParameter('name', $record->name($locale))
            ->setParameter('translitName', $record->translitName($locale))
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
            $localized[$locale->value()] = $values[$locale->value()];
        }

        return $localized;
    }
}
