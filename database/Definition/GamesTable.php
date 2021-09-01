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
 * @phpstan-import-type LocalizedValues from GameRecord
 * @phpstan-import-type LocalizedLinks from GameRecord
 * @phpstan-import-type LocalizedTranslations from GameRecord
 * @phpstan-import-type Counterstops from GameRecord
 */
final class GamesTable extends AbstractTable
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
        Table $companies
    ): Table {
        $games = $schema->createTable('stg_games');
        $games->addColumn('id', 'integer', ['autoincrement' => true]);
        $games->addColumn('created_date', 'datetime');
        $games->addColumn('last_modified_date', 'datetime');
        $games->addColumn('company_id', 'integer');
        $games->addColumn('name_filter', 'string', ['length' => 500]);
        $games->addColumn('counterstops', 'string', ['length' => 100]);
        $games->setPrimaryKey(['id']);
        $games->addForeignKeyConstraint($companies, ['company_id'], ['id']);
        $schemaManager->createTable($games);

        $localeGames = $schema->createTable('stg_games_locale');
        $localeGames->addColumn('game_id', 'integer');
        $localeGames->addColumn('locale', 'string', ['length' => 16]);
        $localeGames->addColumn('name', 'string', ['length' => 100]);
        $localeGames->addColumn('name_translit', 'string', ['length' => 100]);
        $localeGames->addColumn('description', 'string', ['length' => 250]);
        $localeGames->addColumn('links', 'string', ['length' => 1000]);
        $localeGames->addColumn('translations', 'string', ['length' => 500]);
        $localeGames->setPrimaryKey(['game_id', 'locale']);
        $localeGames->addForeignKeyConstraint($games, ['game_id'], ['id']);
        $schemaManager->createTable($localeGames);

        $schemaManager->createView($this->createView());

        return $games;
    }

    private function createView(): View
    {
        $qb = $this->connection->createQueryBuilder();
        $alias = 'x';

        return new View('stg_query_games', $qb->select(
            'id',
            'created_date',
            'last_modified_date',
            'locale',
            'name',
            'name_translit',
            'name_filter',
            'company_id',
            'company_name',
            'company_name_translit',
            'company_name_filter',
            'description',
            'links',
            'counterstops',
            'translations'
        )
            ->from("({$this->gameSql()})", $alias)
            ->getSQL());
    }

    private function gameSql(): string
    {
        $qb = $this->connection->createQueryBuilder();

        return $qb->select(
            'games.id',
            'games.created_date',
            'games.last_modified_date',
            'localized.locale',
            'localized.name',
            'localized.name_translit',
            'games.name_filter',
            'games.company_id',
            'companies.name AS company_name',
            'companies.name_translit AS company_name_translit',
            'companies.name_filter AS company_name_filter',
            'localized.description',
            'localized.links',
            'games.counterstops',
            'localized.translations'
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
            ->getSQL();
    }

    /**
     * @param LocalizedValues $names
     * @param LocalizedValues $translitNames
     * @param LocalizedValues $descriptions
     * @param LocalizedLinks $links
     * @param LocalizedTranslations $translations
     * @param Counterstops $counterstops
     */
    public function createRecord(
        int $companyId,
        array $names,
        array $translitNames = [],
        array $descriptions = [],
        array $links = [],
        array $translations = [],
        array $counterstops = []
    ): GameRecord {
        if ($translitNames == null) {
            $translitNames = $names;
        }
        if ($descriptions == null) {
            $descriptions = $this->emptyLocalizedValues('');
        }
        if ($links == null) {
            $links = $this->emptyLocalizedValues([]);
        }
        if ($translations == null) {
            $translations = $this->emptyLocalizedValues([]);
        }

        return new GameRecord(
            $companyId,
            $this->localizeValues($names),
            $this->localizeValues($translitNames),
            $this->localizeValues($descriptions),
            $this->localizeValues($links),
            $this->localizeValues($translations),
            $counterstops
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
                'name_filter' => ':nameFilter',
                'counterstops' => ':counterstops',
            ])
            ->setParameter('createdDate', DateTime::now())
            ->setParameter('lastModifiedDate', DateTime::now())
            ->setParameter('companyId', $record->companyId())
            ->setParameter('nameFilter', $this->makeNameFilter($record))
            ->setParameter('counterstops', $this->makeCounterstops($record))
            ->executeStatement();

        $record->setId((int)$this->connection->lastInsertId());

        foreach ($this->locales()->all() as $locale) {
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
                'description' => ':description',
                'links' => ':links',
                'translations' => ':translations',
            ])
            ->setParameter('gameId', $record->id())
            ->setParameter('locale', $locale->value())
            ->setParameter('name', $record->name($locale))
            ->setParameter('translitName', $record->translitName($locale))
            ->setParameter('description', $record->description($locale))
            ->setParameter('links', $this->makeLinks($record, $locale))
            ->setParameter('translations', $this->makeTranslations($record, $locale))
            ->executeStatement();
    }

    private function makeNameFilter(GameRecord $record): string
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

    private function makeLinks(GameRecord $record, Locale $locale): string
    {
        return Yaml::dump(
            $record->links($locale)
        );
    }

    private function makeTranslations(GameRecord $record, Locale $locale): string
    {
        return Yaml::dump(
            $record->translations($locale)
        );
    }

    private function makeCounterstops(GameRecord $record): string
    {
        return Yaml::dump(
            $record->counterstops()
        );
    }
}
