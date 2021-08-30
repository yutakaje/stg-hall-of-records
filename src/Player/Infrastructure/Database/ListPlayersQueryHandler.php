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
use Doctrine\DBAL\Query\QueryBuilder;
use Stg\HallOfRecords\Player\Application\Query\ListPlayersQueryHandlerInterface;
use Stg\HallOfRecords\Shared\Application\Query\ListQuery;
use Stg\HallOfRecords\Shared\Application\Query\ListResult;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Infrastructure\Database\QueryApplier;
use Stg\HallOfRecords\Shared\Infrastructure\Database\QueryColumn;

final class ListPlayersQueryHandler implements ListPlayersQueryHandlerInterface
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
            'numScores' => QueryColumn::int('num_scores'),
        ]);
    }

    public function execute(ListQuery $query): ListResult
    {
        return new ListResult(
            $this->readPlayers($query)
        );
    }

    private function readPlayers(ListQuery $query): Resources
    {
        $qb = $this->connection->createQueryBuilder();

        $sql = $this->readPlayersSql($qb, $query);

        $stmt = $this->applier->applyFilter(
            $qb->from("({$sql})")
                ->select(
                    'id',
                    'name',
                    'num_scores'
                ),
            $query->filter()
        )
            ->orderBy('name')
            ->addOrderBy('id')
            ->executeQuery();

        $players = [];

        while (($row = $stmt->fetchAssociative()) !== false) {
            $players[] = $this->createPlayer($row);
        }

        return new Resources($players);
    }

    private function readPlayersSql(
        QueryBuilder $wrapper,
        ListQuery $query
    ): string {
        $qb = $this->connection->createQueryBuilder();

        return $qb->select(
            'id',
            'name',
            'name_filter',
            "({$this->numScoresSql()}) AS num_scores"
        )
            ->from('stg_players', 'players')
            ->getSQL();
    }

    private function numScoresSql(): string
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
