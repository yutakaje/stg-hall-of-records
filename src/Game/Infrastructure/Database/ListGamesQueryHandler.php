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

namespace Stg\HallOfRecords\Game\Infrastructure\Database;

use Doctrine\DBAL\Connection;
use Stg\HallOfRecords\Game\Application\Query\ListGamesQueryHandlerInterface;
use Stg\HallOfRecords\Shared\Application\Query\ListQuery;
use Stg\HallOfRecords\Shared\Application\Query\ListResult;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;

final class ListGamesQueryHandler implements ListGamesQueryHandlerInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(ListQuery $query): ListResult
    {
        return new ListResult(
            $this->readGames($query),
            $query->locale()
        );
    }

    private function readGames(ListQuery $query): Resources
    {
        $qb = $this->connection->createQueryBuilder();

        $stmt = $qb->select('id', 'name', 'company_id', 'company_name')
            ->from('stg_query_games')
            ->where($qb->expr()->eq('locale', ':locale'))
            ->setParameter('locale', $query->locale()->value())
            ->orderBy('name_translit')
            ->addOrderBy('id')
            ->executeQuery();

        $games = [];

        while (($row = $stmt->fetchAssociative()) !== false) {
            $games[] = $this->createGame($row);
        }

        return new Resources($games);
    }

    /**
     * @param Row $row
     */
    private function createGame(array $row): Resource
    {
        $game = new Resource();
        $game->id = $row['id'];
        $game->name = $row['name'];
        $game->company = $this->createCompany($row);

        return $game;
    }

    /**
     * @param Row $row
     */
    private function createCompany(array $row): Resource
    {
        $company = new Resource();
        $company->id = $row['company_id'];
        $company->name = $row['company_name'];

        return $company;
    }
}
