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
use Doctrine\DBAL\Query\QueryBuilder;
use Stg\HallOfRecords\Company\Application\Query\ListCompaniesQueryHandlerInterface;
use Stg\HallOfRecords\Shared\Application\Query\ListQuery;
use Stg\HallOfRecords\Shared\Application\Query\ListResult;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Infrastructure\Database\QueryApplier;
use Stg\HallOfRecords\Shared\Infrastructure\Database\QueryColumn;

final class ListCompaniesQueryHandler implements ListCompaniesQueryHandlerInterface
{
    private Connection $connection;
    private QueryApplier $applier;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->applier = new QueryApplier([
            'id' => QueryColumn::int('id'),
            'name' => QueryColumn::string('name_filter'),
            'numGames' => QueryColumn::int('num_games'),
        ]);
    }

    public function execute(ListQuery $query): ListResult
    {
        return new ListResult(
            $this->readCompanies($query)
        );
    }

    private function readCompanies(ListQuery $query): Resources
    {
        $qb = $this->connection->createQueryBuilder();

        $sql = $this->readCompaniesSql($qb, $query);

        $stmt = $this->applier->applyFilter(
            $qb->from("({$sql})")
                ->select(
                    'id',
                    'name',
                    'num_games'
                ),
            $query->filter()
        )
            ->orderBy('name_translit')
            ->addOrderBy('id')
            ->executeQuery();

        $companies = [];

        while (($row = $stmt->fetchAssociative()) !== false) {
            $companies[] = $this->createCompany($row);
        }

        return new Resources($companies);
    }

    private function readCompaniesSql(
        QueryBuilder $wrapper,
        ListQuery $query
    ): string {
        $qb = $this->connection->createQueryBuilder();

        $wrapper->setParameter('locale', $query->locale()->value());

        return $qb->from('stg_query_companies', 'companies')
            ->select(
                'id',
                'name',
                'name_translit',
                'name_filter',
                "({$this->numGamesSql()}) AS num_games"
            )
            ->where($qb->expr()->eq('locale', ':locale'))
            ->getSQL();
    }

    private function numGamesSql(): string
    {
        $qb = $this->connection->createQueryBuilder();

        return $qb->select('count(*)')
            ->from('stg_games')
            ->where($qb->expr()->eq('company_id', 'companies.id'))
            ->getSQL();
    }

    /**
     * @param Row $row
     */
    private function createCompany(array $row): Resource
    {
        $company = new Resource();
        $company->id = $row['id'];
        $company->name = $row['name'];
        $company->numGames = $row['num_games'];

        return $company;
    }
}
