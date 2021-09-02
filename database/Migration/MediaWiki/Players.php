<?php

/*
 * This file is part of the stg/hall-of-records package.
 *
 * (c) YTK <yutakaje@gmail.com>
 *
n * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Stg\HallOfRecords\Database\Migration\MediaWiki;

use Psr\Log\LoggerInterface;
use Stg\HallOfRecords\Database\Database;
use Stg\HallOfRecords\Database\Definition\PlayerRecord;
use Stg\HallOfRecords\Data\Score\Score;
use Stg\HallOfRecords\Data\Score\ScoreRepositoryInterface;

final class Players
{
    private Database $database;
    private LoggerInterface $logger;
    private ScoreRepositoryInterface $sourceScores;
    /** @var PlayerRecord[] */
    private array $records;

    public function __construct(
        Database $database,
        LoggerInterface $logger,
        ScoreRepositoryInterface $sourceScores
    ) {
        $this->database = $database;
        $this->logger = $logger;
        $this->sourceScores = $sourceScores;
        $this->records = [];
    }

    public function insert(): void
    {
        $this->logger->info('Importing players');

        $start = microtime(true);

        $this->records = $this->createRecords();

        $this->database->players()->insertRecords($this->records);

        $this->logger->info('Players imported', [
            'total' => sizeof($this->records),
            'elapsed' => microtime(true) - $start,
        ]);
    }

    /**
     * @return PlayerRecord[]
     */
    private function createRecords(): array
    {
        $playerNames = $this->sourceScores->all()
            ->map(fn (Score $score) => $score->property('player'));

        return array_map(
            fn (string $name) => $this->createRecord($name),
            array_unique($playerNames)
        );
    }

    private function createRecord(string $name): PlayerRecord
    {
        $this->logger->debug('Creating player', [
            'name' => $name,
        ]);

        $aliases = [];

        if ($name === 'SOF-WTN') {
            $aliases[] = 'WTN';
        }

        return $this->database->players()->createRecord($name, $aliases);
    }

    public function find(string $name): PlayerRecord
    {
        foreach ($this->records as $record) {
            if ($record->name() === $name) {
                return $record;
            }
        }

        throw new \InvalidArgumentException(
            "Player named `{$name}` does not exist."
        );
    }
}
