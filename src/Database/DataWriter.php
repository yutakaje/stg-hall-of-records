<?php

declare(strict_types=1);

/*
 * This file is part of the stg/hall-of-records package.
 *
 * (c) YTK <yutakaje@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stg\HallOfRecords\Database;

use Doctrine\DBAL\Connection;
use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\Games;

final class DataWriter
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function write(Games $games): void
    {
        foreach ($games->iterator() as $game) {
            $this->insertGame($game);
        }
    }

    private function insertGame(Game $game): void
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->insert('games')
            ->values([
                'name' => ':name',
                'company' => ':company',
            ])
            ->setParameter(':name', $game->name())
            ->setParameter(':company', $game->company())
            ->execute();

        $this->insertScores($game);
    }

    public function insertScores(Game $game): void
    {
        foreach ($game->scores()->iterator() as $score) {
            $this->insertScore($game, $score);
        }
    }

    private function insertScore(Game $game, Score $score): void
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->insert('scores')
            ->values([
                'game' => ':game',
                'player' => ':player',
                'score' => ':score',
            ])
            ->setParameter(':game', $game->name())
            ->setParameter(':player', $score->player())
            ->setParameter(':score', $score->score())
            ->execute();
    }
}
