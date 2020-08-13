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

namespace Stg\HallOfRecords\Database;

use Doctrine\DBAL\Connection;
use Stg\HallOfRecords\Import\ParsedGame;
use Stg\HallOfRecords\Import\ParsedScore;

final class ParsedDataWriter
{
    private Connection $connection;
    private int $nextGameId;
    private int $nextScoreId;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->nextGameId = 1;
        $this->nextScoreId = 1;
    }

    /**
     * @param ParsedGame[] $games
     */
    public function write(array $games): void
    {
        foreach ($games as $game) {
            $this->insertGame($game);
        }
    }

    private function insertGame(ParsedGame $game): void
    {
        $gameId = $this->nextGameId++;

        $this->connection->createQueryBuilder()
            ->insert('games')
            ->values([
                'id' => ':id',
                'name' => ':name',
                'company' => ':company',
            ])
            ->setParameter(':id', $gameId)
            ->setParameter(':name', $game->name())
            ->setParameter(':company', $game->company())
            ->execute();

        $this->insertScores($gameId, $game->scores());
    }

    /**
     * @param ParsedScore[] $scores
     */
    public function insertScores(int $gameId, array $scores): void
    {
        foreach ($scores as $score) {
            $this->insertScore($gameId, $score);
        }
    }

    private function insertScore(int $gameId, ParsedScore $score): void
    {
        $scoreId = $this->nextScoreId++;

        $this->connection->createQueryBuilder()
            ->insert('scores')
            ->values([
                'id' => ':id',
                'game_id' => ':gameId',
                'player' => ':player',
                'score' => ':score',
                'ship' => ':ship',
                'mode' => ':mode',
                'weapon' => ':weapon',
                'scored_date' => ':scoredDate',
                'source' => ':source',
                'comments' => ':comments',
            ])
            ->setParameter(':id', $scoreId)
            ->setParameter(':gameId', $gameId)
            ->setParameter(':player', $score->player())
            ->setParameter(':score', $score->score())
            ->setParameter(':ship', $score->ship())
            ->setParameter(':mode', $score->mode())
            ->setParameter(':weapon', $score->weapon())
            ->setParameter(':scoredDate', $score->scoredDate())
            ->setParameter(':source', $score->source())
            ->setParameter(':comments', json_encode($score->comments()))
            ->execute();
    }
}
