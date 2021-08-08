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
use Stg\HallOfRecords\Player\Application\Query\ViewPlayerQueryHandlerInterface;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\ViewQuery;
use Stg\HallOfRecords\Shared\Application\Query\ViewResult;
use Stg\HallOfRecords\Shared\Infrastructure\Error\ResourceNotFoundException;

final class ViewPlayerQueryHandler implements ViewPlayerQueryHandlerInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(ViewQuery $query): ViewResult
    {
        $player = $this->readPlayer($query);
        $player->aliases = $this->readAliases($query);

        return new ViewResult(
            $player,
            $query->locale()
        );
    }

    private function readPlayer(ViewQuery $query): Resource
    {
        $qb = $this->connection->createQueryBuilder();

        $stmt = $qb->select('id', 'name')
            ->from('stg_players')
            ->where($qb->expr()->eq('id', ':id'))
            ->setParameter('id', $query->id())
            ->executeQuery();

        $row = $stmt->fetchAssociative();

        if ($row === false) {
            throw new ResourceNotFoundException('Player not found');
        }

        return $this->createPlayer($row);
    }

    /**
     * @return string[]
     */
    private function readAliases(ViewQuery $query): array
    {
        $qb = $this->connection->createQueryBuilder();

        $stmt = $qb->select('alias')
            ->from('stg_player_aliases')
            ->where($qb->expr()->eq('player_id', ':playerId'))
            ->setParameter('playerId', $query->id())
            ->orderBy('alias')
            ->addOrderBy('id')
            ->executeQuery();

        $aliases = [];

        while (($row = $stmt->fetchAssociative()) !== false) {
            $aliases[] = $row['alias'];
        }

        return $aliases;
    }

    /**
     * @param Row $row
     */
    private function createPlayer(array $row): Resource
    {
        $player = new Resource();
        $player->id = $row['id'];
        $player->name = $row['name'];

        return $player;
    }
}
