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
use Doctrine\DBAL\Query\QueryBuilder;
use Stg\HallOfRecords\Game\Application\Query\ListGamesQueryHandlerInterface;
use Stg\HallOfRecords\Shared\Application\Query\ListQuery;
use Stg\HallOfRecords\Shared\Application\Query\ListResult;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Application\ResultMessage;
use Stg\HallOfRecords\Shared\Infrastructure\Database\QueryApplier;
use Stg\HallOfRecords\Shared\Infrastructure\Database\QueryColumn;

final class ListGamesQueryHandler implements ListGamesQueryHandlerInterface
{
    private Connection $connection;
    private QueryApplier $applier;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->applier = new QueryApplier([
            'id' => QueryColumn::int('id'),
            'name' => QueryColumn::oneOf(
                QueryColumn::string('name'),
                QueryColumn::string('name_filter'),
            ),
            'company.id' => QueryColumn::int('company_id'),
            'company.name' => QueryColumn::string('company_name_filter'),
            'numScores' => QueryColumn::int('num_scores'),
        ]);
    }

    public function execute(ListQuery $query): ListResult
    {
        $games = $this->readGames($query);

        return new ListResult(
            $games,
            $this->createResultMessage()
        );
    }

    private function readGames(ListQuery $query): Resources
    {
        $qb = $this->connection->createQueryBuilder();

        $sql = $this->readGamesSql($qb, $query);

        $stmt = $this->applier->applyFilter(
            $qb->from("({$sql})")
                ->select(
                    'id',
                    'name',
                    'company_id',
                    'company_name',
                    'num_scores'
                ),
            $query->filter()
        )
            ->orderBy('name_translit')
            ->addOrderBy('id')
            ->executeQuery();

        $games = [];

        while (($row = $stmt->fetchAssociative()) !== false) {
            $games[] = $this->createGame($row);
        }

        return new Resources($games);
    }

    private function readGamesSql(
        QueryBuilder $wrapper,
        ListQuery $query
    ): string {
        $qb = $this->connection->createQueryBuilder();

        $wrapper->setParameter('locale', $query->locale()->value());

        return $qb->from('stg_query_games', 'games')
            ->select(
                'id',
                'name',
                'name_translit',
                'name_filter',
                'company_id',
                'company_name',
                'company_name_translit',
                'company_name_filter',
                "({$this->numScoresQuery()}) AS num_scores"
            )
            ->where($qb->expr()->eq('locale', ':locale'))
            ->getSQL();
    }

    private function numScoresQuery(): string
    {
        $qb = $this->connection->createQueryBuilder();

        return $qb->select('count(*)')
            ->from('stg_scores')
            ->where($qb->expr()->eq('game_id', 'games.id'))
            ->getSQL();
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
        $game->numScores = $row['num_scores'];

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

    private function createResultMessage(): ?ResultMessage
    {
        if (!$this->applier->containsError()) {
            return null;
        }

        return ResultMessage::warning($this->applier->errorMessage());
    }
}
