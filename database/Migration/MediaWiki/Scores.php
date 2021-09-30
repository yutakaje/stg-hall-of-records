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

namespace Stg\HallOfRecords\Database\Migration\MediaWiki;

use Psr\Log\LoggerInterface;
use Stg\HallOfRecords\Database\Database;
use Stg\HallOfRecords\Database\Definition\ScoreAttributeRecord;
use Stg\HallOfRecords\Database\Definition\ScoreRecord;
use Stg\HallOfRecords\Data\Score\Score;
use Stg\HallOfRecords\Data\Score\ScoreRepositoryInterface;
use Stg\HallOfRecords\Data\Setting\SettingRepositoryInterface;

/**
 * @phpstan-import-type Source from ScoreRecord
 * @phpstan-import-type Sources from ScoreRecord
 * @phpstan-type SourceTranslation array{en:string, ja:string}
 * @phpstan-type SourceTranslations array<string,SourceTranslation>
 */
final class Scores
{
    private Database $database;
    private LoggerInterface $logger;
    private Games $games;
    private Players $players;
    private ScoreRepositoryInterface $sourceScores;
    private SettingRepositoryInterface $sourceSettings;
    private bool $checkForUnhandledProperties;
    /** @var SourceTranslations */
    private array $sourceTranslations;
    /** @var ScoreRecord[] */
    private array $records;

    public function __construct(
        Database $database,
        LoggerInterface $logger,
        Games $games,
        Players $players,
        ScoreRepositoryInterface $sourceScores,
        SettingRepositoryInterface $sourceSettings,
        bool $checkForUnhandledProperties
    ) {
        $this->database = $database;
        $this->logger = $logger;
        $this->games = $games;
        $this->players = $players;
        $this->sourceScores = $sourceScores;
        $this->sourceSettings = $sourceSettings;
        $this->checkForUnhandledProperties = $checkForUnhandledProperties;
        $this->sourceTranslations = [];
        $this->records = [];
    }

    public function insert(): void
    {
        $this->logger->info('Importing scores');

        $start = microtime(true);

        $this->sourceTranslations = $this->createSourceTranslations();
        $this->records = $this->createRecords();

        $this->database->scores()->insertRecords($this->records);

        $this->logger->info('Scores imported', [
            'total' => sizeof($this->records),
            'elapsed' => microtime(true) - $start,
        ]);
    }

    /**
     * @return SourceTranslations
     */
    private function createSourceTranslations(): array
    {
        $translations = array_filter(
            $this->sourceSettings->filterGlobal()->get('translations'),
            fn (array $entry) => $entry['property'] === 'sources'
        );

        return array_reduce(
            $translations,
            fn (array $all, array $translation) => array_merge(
                $all,
                $this->createSourceTranslation($translation)
            ),
            []
        );
    }

    /**
     * @param array<string,mixed> $translation
     * @return SourceTranslations
     */
    private function createSourceTranslation(array $translation): array
    {
        $properties = new Properties($translation);

        $value = (string)$properties->consume('value');
        $enTranslation = (string)$properties->consume('value-en', $value);
        $jaTranslation = (string)$properties->consume('value-jp', $value);

        $properties->remove('property', 'fuzzy-match');

        if (!$this->checkForUnhandledProperties) {
            $properties->assertEmpty();
        }

        return [
            strtolower($value) => [
                'en' => $enTranslation,
                'ja' => $jaTranslation,
            ],
        ];
    }

    /**
     * @return ScoreRecord[]
     */
    private function createRecords(): array
    {
        return $this->sourceScores->all()
            ->map(fn (Score $score) => $this->createRecord($score));
    }

    private function createRecord(Score $score): ScoreRecord
    {
        $this->logger->debug('Creating score', $score->properties());

        $properties = new Properties($score->properties());

        $playerName = $properties->consume('player');
        if ($playerName === '') {
            throw new \InvalidArgumentException(
                'Player name should not be empty'
            );
        }
        $playerName = (string)$playerName;

        $scoreValue = $properties->consume('score');
        $realScoreValue = $properties->consume('score-real', $scoreValue);
        $sortScoreValue =  $properties->consume(
            'score-sort',
            $this->createSortScoreValue($realScoreValue)
        );
        $sources = $properties->consume('sources', []);

        $attributes = array_filter([
            'ship' => $properties->consume('ship', null),
            'mode' => $properties->consume('mode', null),
            'weapon' => $properties->consume('weapon', null),
            'loop' => $properties->consume('loop', null),
            'version' => $properties->consume('version', null),
            'autofire' => $properties->consume('autofire', null),
        ], fn ($value) => $value !== null);

        $properties->remove('id', 'game-id');

        /* @TODO Handle remaining properties */
        $properties->remove(
            'is-current-record',
            'comments',
            'comments-jp',
            'attributes',
            'platform',
            'image-urls',
            'manual-sort',
            'difficulty',
            'game',
        );

        if ($this->checkForUnhandledProperties) {
            $properties->assertEmpty();
        }

        return $this->database->scores()->createRecord(
            $this->games->find($score->gameId())->id(),
            $this->players->find($playerName)->id(),
            $playerName,
            $scoreValue,
            $realScoreValue,
            $sortScoreValue,
            [
                'en' => $this->createSources('en', $sources),
                'ja' => $this->createSources('ja', $sources),
            ],
            array_map(
                fn (string $name, string $value) => $this->createAttributeRecord(
                    $name,
                    $value
                ),
                array_keys($attributes),
                $attributes
            )
        );
    }

    private function createAttributeRecord(
        string $name,
        string $value
    ): ScoreAttributeRecord {
        return $this->database->scores()->attributes()->createRecord(
            $name,
            $value,
            [
                'en' => $this->translateAttribute('en', $name, $value),
                'ja' => $this->translateAttribute('ja', $name, $value),
            ],
        );
    }

    private function createSortScoreValue(string $scoreValue): string
    {
        return str_replace(',', '', $scoreValue);
    }

    /**
     * @param array<string,mixed>[] $sources
     * @return Sources
     */
    private function createSources(string $locale, array $sources): array
    {
        return array_map(
            fn (array $source) => $this->createSource($locale, $source),
            $sources
        );
    }

    /**
     * @param array<string,mixed> $source
     * @return Source
     */
    private function createSource(string $locale, array $source): array
    {
        $properties = new Properties($source);

        $name = $this->translateSource($properties->consume('name'), $locale);
        $date = $properties->consume('date', '');
        $url = $properties->consume('url', '');

        if (!$this->checkForUnhandledProperties) {
            $properties->assertEmpty();
        }

        return [
            'name' => $name,
            'date' => $date,
            'url' => $url,
        ];
    }

    private function translateSource(string $name, string $locale): string
    {
        $lookup = strtolower($name);

        if (!isset($this->sourceTranslations[$lookup][$locale])) {
            return $name;
        }

        return $this->sourceTranslations[$lookup][$locale];
    }

    private function translateAttribute(
        string $locale,
        string $name,
        string $value
    ): string {
        /* @TODO translate attribute */
        return $value;
    }
}
