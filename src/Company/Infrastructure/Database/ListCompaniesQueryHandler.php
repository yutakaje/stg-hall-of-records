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
use Stg\HallOfRecords\Company\Application\Query\ListCompaniesQueryHandlerInterface;
use Stg\HallOfRecords\Shared\Application\Query\ListQuery;
use Stg\HallOfRecords\Shared\Application\Query\ListResult;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;

final class ListCompaniesQueryHandler implements ListCompaniesQueryHandlerInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(ListQuery $query): ListResult
    {
        return new ListResult(
            $this->readCompanies($query),
            $query->locale()
        );
    }

    private function readCompanies(ListQuery $query): Resources
    {
        $qb = $this->connection->createQueryBuilder();

        $stmt = $qb->select('id', 'name')
            ->from('stg_query_companies')
            ->where($qb->expr()->eq('locale', ':locale'))
            ->setParameter('locale', $query->locale())
            ->orderBy('name')
            ->addOrderBy('id')
            ->executeQuery();

        $companies = [];

        while (($row = $stmt->fetchAssociative()) !== false) {
            $companies[] = $this->createCompany($row);
        }

        return new Resources($companies);
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
}