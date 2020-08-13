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
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    final protected function connection(): Connection
    {
        return $this->connection;
    }

    /**
     * @param mixed $order
     */
    protected function addOrderBy(
        QueryBuilder $qb,
        string $name,
        $order
    ): void {
        if ($order === 'asc') {
            $qb->addOrderBy($name, 'asc');
        } elseif ($order === 'desc') {
            $qb->addOrderBy($name, 'desc');
        } elseif (is_array($order)) {
            $this->addCustomOrder($qb, $name, array_values(
                array_filter($order, fn ($value) => is_string($value))
            ));
        }
    }

    /**
     * @param string[] $order
     */
    private function addCustomOrder(
        QueryBuilder $qb,
        string $name,
        array $order
    ): void {
        if ($order == null) {
            return;
        }

        $customOrder = [];

        foreach ($order as $index => $value) {
            $placeholder = ":{$name}_{$index}";
            $customOrder[] = "WHEN {$name} = {$placeholder} THEN {$index}";
            $qb->setParameter($placeholder, $value);
        }

        $customOrder[] = 'ELSE ' . sizeof($customOrder);

        $qb->addOrderBy('CASE ' . implode(' ', $customOrder) . ' END');
    }
}
