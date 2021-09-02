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

        if ($this->checkForUnhandledProperties) {
            $properties->assertEmpty();
        }

        return $this->database->scores()->createRecord(
            $this->games->find($score->gameId())->id(),
            $this->players->find($playerName)->id(),
            $playerName,
            $scoreValue
        );
    }
}
