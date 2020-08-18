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

use Stg\HallOfRecords\Data\Sorting\ArraySorter;
use Stg\HallOfRecords\Data\Sorting\SortableInterface;

/**
 * @template Item of SortableInterface
 */
abstract class AbstractRepository
{
    /**
     * @param Item[] $items
     * @param array<string,mixed> $sort
     * @return Item[]
     */
    protected function sortItems(array $items, array $sort): array
    {
        /** @var ArraySorter<Item> */
        $sorter = new ArraySorter();
        return $sorter->sort($items, $sort);
    }
}
