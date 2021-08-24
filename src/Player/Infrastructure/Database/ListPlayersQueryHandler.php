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

namespace Stg\HallOfRecords\Player\Infrastructure\Database;

use Doctrine\DBAL\Connection;
use Stg\HallOfRecords\Player\Application\Query\ListPlayersQueryHandlerInterface;
use Stg\HallOfRecords\Shared\Application\Query\ListQuery;
use Stg\HallOfRecords\Shared\Application\Query\ListResult;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;

final class ListPlayersQueryHandler implements ListPlayersQueryHandlerInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(ListQuery $query): ListResult
    {
        return new ListResult(
            $this->readPlayers($query),
            $query->locale()
        );
    }

    private function readPlayers(ListQuery $query): Resources
    {
        $qb = $this->connection->createQueryBuilder();

        $stmt = $qb->select(
            'id',
            'name',
            "({$this->numScoresQuery()}) AS num_scores"
        )
            ->from('stg_players', 'players')
            ->orderBy('name')
            ->addOrderBy('id')
            ->executeQuery();

        $players = [];

        while (($row = $stmt->fetchAssociative()) !== false) {
            $players[] = $this->createPlayer($row);
        }

        return new Resources($players);
    }

    private function numScoresQuery(): string
    {
        $qb = $this->connection->createQueryBuilder();

        return $qb->select('count(*)')
            ->from('stg_scores')
            ->where($qb->expr()->eq('player_id', 'players.id'))
            ->getSQL();
    }

    /**
     * @param Row $row
     */
    private function createPlayer(array $row): Resource
    {
        $player = new Resource();
        $player->id = $row['id'];
        $player->name = $row['name'];
        $player->numScores = $row['num_scores'];

        return $player;
    }
}
