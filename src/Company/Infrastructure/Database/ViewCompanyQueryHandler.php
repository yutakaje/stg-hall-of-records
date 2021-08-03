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
        return new ViewResult(
            $this->readCompany($query),
            $query->locale()
        );
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
            ->setParameter('locale', $query->locale())
            ->executeQuery();

        $row = $stmt->fetchAssociative();

        if ($row === false) {
            throw new ResourceNotFoundException('Company not found');
        }

        return $this->createCompany($row);
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
