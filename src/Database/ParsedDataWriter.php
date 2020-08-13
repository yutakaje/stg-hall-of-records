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
use Stg\HallOfRecords\Import\ParsedData;
use Stg\HallOfRecords\Import\ParsedGame;
use Stg\HallOfRecords\Import\ParsedScore;

final class ParsedDataWriter
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function write(ParsedData $data): void
    {
        foreach ($data->games() as $game) {
            $this->insertGame($game);
        }
    }

    private function insertGame(ParsedGame $game): void
    {
        $this->connection->createQueryBuilder()
            ->insert('games')
            ->values([
                'id' => ':id',
                'name' => ':name',
                'company' => ':company',
            ])
            ->setParameter(':id', $game->id())
            ->setParameter(':name', $game->name())
            ->setParameter(':company', $game->company())
            ->execute();

        $this->insertScores($game);
    }

    public function insertScores(ParsedGame $game): void
    {
        foreach ($game->scores() as $score) {
            $this->insertScore($game, $score);
        }
    }

    private function insertScore(ParsedGame $game, ParsedScore $score): void
    {
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
            ->setParameter(':id', $score->id())
            ->setParameter(':gameId', $game->id())
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
