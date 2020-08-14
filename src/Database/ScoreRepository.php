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

namespace Stg\HallOfRecords\Database;

use Doctrine\DBAL\Connection;
use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\ScoreFactory;
use Stg\HallOfRecords\Data\ScoreRepositoryInterface;
use Stg\HallOfRecords\Data\Scores;

final class ScoreRepository extends AbstractRepository implements ScoreRepositoryInterface
{
    private ScoreFactory $scoreFactory;

    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
        $this->scoreFactory = new ScoreFactory();
    }

    /**
     * @param array<string,mixed> $sort
     */
    public function filterByGame(Game $game, array $sort = []): Scores
    {
        $qb = $this->connection()->createQueryBuilder();

        $columns = [
            'id',
            'player',
            'score',
            'ship',
            'mode',
            'weapon',
            'scored_date',
            'source',
            'comments',
        ];

        $stmt = $qb->select(...$columns)
            ->from('scores')
            ->where($qb->expr()->eq('game_id', ':gameId'))
            ->setParameter(':gameId', $game->id());

        foreach ($sort as $name => $order) {
            if (in_array($name, $columns, true)) {
                $this->addOrderBy($qb, $name, $order);
            }
        }

        // Sort by id to always get a distinct order.
        $stmt = $qb->addOrderBy('id')
            ->execute();

        $scores = [];

        while (($columns = $stmt->fetch()) !== false) {
            $scores[] = $this->scoreFactory->create(
                (int)$columns['id'],
                $game->id(),
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
