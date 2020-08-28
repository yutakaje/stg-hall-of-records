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
final class ArrayGrouper
{
    /**
     * @param Item[] $items
     * @param string[] $group
     * @return Item[][]
     */
    public function group(array $items, array $group): array
    {
        $propertyName = array_shift($group);

        if ($propertyName === null) {
            return [
                $items
            ];
        }

        $grouped = [];

        foreach ($items as $item) {
            $grouped[$item->getProperty($propertyName)][] = $item;
        }


        return array_reduce(
            $grouped,
            fn (array $mergedItems, array $groupedItems) => array_merge(
                $mergedItems,
                $this->group($groupedItems, $group)
            ),
            []
        );
    }
}
