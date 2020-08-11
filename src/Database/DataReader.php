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
use Stg\HallOfRecords\Data\GameFactory;
use Stg\HallOfRecords\Data\Games;
use Stg\HallOfRecords\Data\ScoreFactory;
use Stg\HallOfRecords\Data\Scores;

final class DataReader
{
    private Connection $connection;
    private GameFactory $gameFactory;
    private ScoreFactory $scoreFactory;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->gameFactory = new GameFactory();
        $this->scoreFactory = new ScoreFactory();
    }

    public function read(): Games
    {
        return $this->readGames();
    }

    private function readGames(): Games
    {
        $qb = $this->connection->createQueryBuilder();

        $stmt = $qb->select('id', 'name', 'company')
            ->from('games')
            ->execute();

        $games = [];

        while (($columns = $stmt->fetch()) !== false) {
            $id = (int)$columns['id'];
            $games[] = $this->gameFactory->create(
                $id,
                $columns['name'],
                $columns['company'],
                $this->readScores($id)
            );
        }

        return new Games($games);
    }

    private function readScores(int $gameId): Scores
    {
        $qb = $this->connection->createQueryBuilder();

        $stmt = $qb->select(
            'id',
            'player',
            'score',
            'ship',
            'mode',
            'weapon',
            'scored_date',
            'source',
            'comments'
        )
            ->from('scores')
            ->where($qb->expr()->eq('game_id', ':gameId'))
            ->setParameter(':gameId', $gameId)
            ->execute();

        $scores = [];

        while (($columns = $stmt->fetch()) !== false) {
            $scores[] = $this->scoreFactory->create(
                (int)$columns['id'],
                $columns['player'],
                $columns['score'],
                $columns['ship'],
                $columns['mode'],
                $columns['weapon'],
                $columns['scored_date'],
                $columns['source'],
                json_decode($columns['comments']),
            );
        }

        return new Scores($scores);
    }
}
