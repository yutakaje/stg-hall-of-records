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
 * @phpstan-import-type LocalizedSources from ScoreRecord
 * @phpstan-import-type Attributes from ScoreRecord
 */
final class ScoresTable extends AbstractTable
{
    private Connection $connection;
    private ScoreAttributesTable $attributes;

    public function __construct(
        Connection $connection,
        Locales $locales
    ) {
        parent::__construct($locales);
        $this->connection = $connection;
        $this->attributes = new ScoreAttributesTable($this->connection, $locales);
    }

    public function attributes(): ScoreAttributesTable
    {
        return $this->attributes;
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
        $scores->addColumn('score_value', 'string', ['length' => 50]);
        $scores->addColumn('score_value_real', 'string', ['length' => 50]);
        $scores->addColumn('score_value_sort', 'integer');
        $scores->setPrimaryKey(['id']);
        $scores->addForeignKeyConstraint($games, ['game_id'], ['id']);
        $scores->addForeignKeyConstraint($players, ['player_id'], ['id']);
        $schemaManager->createTable($scores);

        $localeScores = $schema->createTable('stg_scores_locale');
        $localeScores->addColumn('score_id', 'integer');
        $localeScores->addColumn('locale', 'string', ['length' => 16]);
        $localeScores->addColumn('sources', 'string', ['length' => 1000]);
        $localeScores->setPrimaryKey(['score_id', 'locale']);
        $localeScores->addForeignKeyConstraint($scores, ['score_id'], ['id']);
        $schemaManager->createTable($localeScores);

        $schemaManager->createView($this->createView());

        $this->attributes->createObjects($schemaManager, $schema, $scores);

        return $scores;
    }

    private function createView(): View
    {
        $qb = $this->connection->createQueryBuilder();
        $alias = 'x';

        return new View('stg_query_scores', $qb->select(
            'id',
            'created_date',
            'last_modified_date',
            'game_id',
            'locale',
            'game_name',
            'game_name_translit',
            'game_name_filter',
            'company_id',
            'company_name',
            'company_name_translit',
            'company_name_filter',
            'player_id',
            'player_name',
            'score_value',
            'score_value_real',
            'score_value_sort',
            'sources'
        )
            ->from("({$this->scoreSql()})", $alias)
            ->getSQL());
    }

    private function scoreSql(): string
    {
        $qb = $this->connection->createQueryBuilder();

        return $qb->select(
            'scores.id',
            'scores.created_date',
            'scores.last_modified_date',
            'localized.locale',
            'scores.game_id',
            'games.name AS game_name',
            'games.name_translit AS game_name_translit',
            'games.name_filter AS game_name_filter',
            'games.company_id',
            'games.company_name',
            'games.company_name_translit',
            'games.company_name_filter',
            'scores.player_id',
            'scores.player_name',
            'scores.score_value',
            'scores.score_value_real',
            'scores.score_value_sort',
            'localized.sources'
        )
            ->from('stg_scores_locale', 'localized')
            ->join(
                'localized',
                'stg_scores',
                'scores',
                $qb->expr()->eq('scores.id', 'localized.score_id')
            )
            ->join(
                'scores',
                'stg_query_games',
                'games',
                (string)$qb->expr()->and(
                    $qb->expr()->eq('games.id', 'scores.game_id'),
                    $qb->expr()->eq('games.locale', 'localized.locale')
                )
            )
            ->getSQL();
    }

    /**
     * @param LocalizedSources $sources
     * @param Attributes $attributes
     */
    public function createRecord(
        int $gameId,
        int $playerId,
        string $playerName,
        string $scoreValue,
        string $realScoreValue = '',
        string $sortScoreValue = '',
        array $sources = [],
        array $attributes = []
    ): ScoreRecord {
        if ($realScoreValue === '') {
            $realScoreValue = $scoreValue;
        }
        if ($sortScoreValue === '') {
            $sortScoreValue = $this->convertScoreValueToNumber($realScoreValue);
        }
        if ($sources == null) {
            $sources = $this->emptyLocalizedValues([]);
        }

        return new ScoreRecord(
            $gameId,
            $playerId,
            $playerName,
            $scoreValue,
            $realScoreValue,
            $sortScoreValue,
            $this->localizeValues($sources),
            $attributes
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
                'score_value_real' => ':realScoreValue',
                'score_value_sort' => ':sortScoreValue',
            ])
            ->setParameter('createdDate', DateTime::now())
            ->setParameter('lastModifiedDate', DateTime::now())
            ->setParameter('gameId', $record->gameId())
            ->setParameter('playerId', $record->playerId())
            ->setParameter('playerName', $record->playerName())
            ->setParameter('scoreValue', $record->scoreValue())
            ->setParameter('realScoreValue', $record->realScoreValue())
            ->setParameter('sortScoreValue', $record->sortScoreValue())
            ->executeStatement();

        $record->setId((int)$this->connection->lastInsertId());

        foreach ($this->locales()->all() as $locale) {
            $this->insertLocalizedRecord($record, $locale);
        }

        foreach ($record->attributes() as $attributeRecord) {
            $attributeRecord->setScoreId($record->id());
            $this->attributes->insertRecord($attributeRecord);
        }
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

    private function insertLocalizedRecord(
        ScoreRecord $record,
        Locale $locale
    ): void {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('stg_scores_locale')
            ->values([
                'score_id' => ':scoreId',
                'locale' => ':locale',
                'sources' => ':sources',
            ])
            ->setParameter('scoreId', $record->id())
            ->setParameter('locale', $locale->value())
            ->setParameter('sources', $this->makeSources($record, $locale))
            ->executeStatement();
    }

    private function makeSources(ScoreRecord $record, Locale $locale): string
    {
        return Yaml::dump(
            $record->sources($locale)
        );
    }

    private function convertScoreValueToNumber(string $scoreValue): string
    {
        return str_replace(',', '', $scoreValue);
    }
}
