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
use Stg\HallOfRecords\Database\Definition\ScoreRecord;
use Stg\HallOfRecords\Data\Score\Score;
use Stg\HallOfRecords\Data\Score\ScoreRepositoryInterface;

/**
 * @phpstan-import-type Source from ScoreRecord
 * @phpstan-import-type Sources from ScoreRecord
 */
final class Scores
{
    private Database $database;
    private LoggerInterface $logger;
    private Games $games;
    private Players $players;
    private ScoreRepositoryInterface $sourceScores;
    private bool $checkForUnhandledProperties;
    /** @var ScoreRecord[] */
    private array $records;

    public function __construct(
        Database $database,
        LoggerInterface $logger,
        Games $games,
        Players $players,
        ScoreRepositoryInterface $sourceScores,
        bool $checkForUnhandledProperties
    ) {
        $this->database = $database;
        $this->logger = $logger;
        $this->games = $games;
        $this->players = $players;
        $this->sourceScores = $sourceScores;
        $this->checkForUnhandledProperties = $checkForUnhandledProperties;
        $this->records = [];
    }

    public function insert(): void
    {
        $this->logger->info('Importing scores');

        $start = microtime(true);

        $this->records = $this->createRecords();

        $this->database->scores()->insertRecords($this->records);

        $this->logger->info('Scores imported', [
            'total' => sizeof($this->records),
            'elapsed' => microtime(true) - $start,
        ]);
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

        $properties->remove('id', 'game-id');

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
                'ja' => $this->createSources('jp', $sources),
            ]
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

        $name = $properties->consume('name');
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
}
