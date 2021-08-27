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

namespace Stg\HallOfRecords\Company\Infrastructure\Database;

use Doctrine\DBAL\Connection;
use Stg\HallOfRecords\Company\Application\Query\ViewCompanyQueryHandlerInterface;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Application\Query\ViewQuery;
use Stg\HallOfRecords\Shared\Application\Query\ViewResult;
use Stg\HallOfRecords\Shared\Infrastructure\Error\ResourceNotFoundException;

final class ViewCompanyQueryHandler implements ViewCompanyQueryHandlerInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(ViewQuery $query): ViewResult
    {
        $company = $this->readCompany($query);
        $company->games = $this->readGames($query);

        return new ViewResult($company);
    }

    private function readCompany(ViewQuery $query): Resource
    {
        $qb = $this->connection->createQueryBuilder();

        $stmt = $qb->select('id', 'name')
            ->from('stg_query_companies')
            ->where($qb->expr()->and(
                $qb->expr()->eq('id', ':id'),
                $qb->expr()->eq('locale', ':locale')
            ))
            ->setParameter('id', $query->id())
            ->setParameter('locale', $query->locale()->value())
            ->executeQuery();

        $row = $stmt->fetchAssociative();

        if ($row === false) {
            throw new ResourceNotFoundException('Company not found');
        }

        return $this->createCompany($row);
    }

    private function readGames(ViewQuery $query): Resources
    {
        $qb = $this->connection->createQueryBuilder();

        $stmt = $qb->select('id', 'name')
            ->from('stg_query_games')
            ->where($qb->expr()->and(
                $qb->expr()->eq('company_id', ':companyId'),
                $qb->expr()->eq('locale', ':locale')
            ))
            ->setParameter('companyId', $query->id())
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
    private function createCompany(array $row): Resource
    {
        $company = new Resource();
        $company->id = $row['id'];
        $company->name = $row['name'];

        return $company;
    }

    /**
     * @param Row $row
     */
    private function createGame(array $row): Resource
    {
        $game = new Resource();
        $game->id = $row['id'];
        $game->name = $row['name'];

        return $game;
    }
}
