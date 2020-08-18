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

namespace Stg\HallOfRecords\Data\Sorting;

/**
 * @template Item of SortableInterface
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
            SortableInterface $lhs,
            SortableInterface $rhs
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
        SortableInterface $lhs,
        SortableInterface $rhs,
        string $propertyName
    ): int {
        return $lhs->getProperty($propertyName) <=> $rhs->getProperty($propertyName);
    }

    private function sortDescending(
        SortableInterface $lhs,
        SortableInterface $rhs,
        string $propertyName
    ): int {
        return $rhs->getProperty($propertyName) <=> $lhs->getProperty($propertyName);
    }

    /**
     * @param string[] $order
     */
    private function sortCustom(
        SortableInterface $lhs,
        SortableInterface $rhs,
        string $propertyName,
        array $order
    ): int {
        if ($order == null) {
            return 0;
        }

        $valuePriorities = array_flip(array_values($order));

        $lhsValue = $lhs->getProperty($propertyName);
        $lhsPriority = $valuePriorities[$lhsValue] ?? -1;

        $rhsValue = $rhs->getProperty($propertyName);
        $rhsPriority = $valuePriorities[$rhsValue] ?? -1;

        return $lhsPriority <=> $rhsPriority;
    }
}
