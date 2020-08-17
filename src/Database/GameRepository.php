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

use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Games;

final class GameRepository extends AbstractRepository implements GameRepositoryInterface
{
    /**
     * @param array<string,mixed> $sort
     */
    public function all(array $sort = []): Games
    {
        $qb = $this->connection()->createQueryBuilder();

        $columns = [
            'id',
            'name',
            'company',
        ];

        $qb->select(...$columns)
            ->from('games');

        foreach ($sort as $name => $order) {
            if (in_array($name, $columns, true)) {
                $this->addOrderBy($qb, $name, $order);
            }
        }

        // Sort by id to always get a distinct order.
        $stmt = $qb->addOrderBy('id')
            ->execute();

        $games = [];

        while (($columns = $stmt->fetch()) !== false) {
            $games[] = new Game(
                (int)$columns['id'],
                $columns['name'],
                $columns['company']
            );
        }

        return new Games($games);
    }
}
