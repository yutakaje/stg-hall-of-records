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

namespace Stg\HallOfRecords\Data;

/**
 * @template Item of ItemInterface
 */
final class ArraySorter
{
    /**
     * @param Item[] $items
     * @param array<string,mixed> $sort
     * @return Item[]
     */
    public function sort(array $items, array $sort): array
    {
        usort($items, function (
            ItemInterface $lhs,
            ItemInterface $rhs
        ) use ($sort): int {
            foreach ($sort as $name => $order) {
                if ($order === 'asc') {
                    $cmp = $this->sortAscending($lhs, $rhs, $name);
                } elseif ($order === 'desc') {
                    $cmp = $this->sortDescending($lhs, $rhs, $name);
                } elseif (is_array($order)) {
                    $cmp = $this->sortCustom($lhs, $rhs, $name, array_values(
                        array_filter($order, fn ($value) => is_string($value))
                    ));
                } else {
                    $cmp = 0;
                }

                if ($cmp !== 0) {
                    return $cmp;
                }
            }

            return 0;
        });

        return $items;
    }

    private function sortAscending(
        ItemInterface $lhs,
        ItemInterface $rhs,
        string $propertyName
    ): int {
        return $lhs->property($propertyName) <=> $rhs->property($propertyName);
    }

    private function sortDescending(
        ItemInterface $lhs,
        ItemInterface $rhs,
        string $propertyName
    ): int {
        return $rhs->property($propertyName) <=> $lhs->property($propertyName);
    }

    /**
     * @param string[] $order
     */
    private function sortCustom(
        ItemInterface $lhs,
        ItemInterface $rhs,
        string $propertyName,
        array $order
    ): int {
        if ($order == null) {
            return 0;
        }

        // Values which are not listed explicitly are put at the end of the list.
        $valuePriorities = array_flip(array_values($order));

        $lhsValue = $lhs->property($propertyName);
        $lhsPriority = $valuePriorities[$lhsValue] ?? PHP_INT_MAX;

        $rhsValue = $rhs->property($propertyName);
        $rhsPriority = $valuePriorities[$rhsValue] ?? PHP_INT_MAX;

        return $lhsPriority <=> $rhsPriority;
    }
}
