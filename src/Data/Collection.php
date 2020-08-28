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
 * @template Item
 */
abstract class Collection
{
    /** @var Item[] */
    private array $items;

    /**
     * @param Item[] $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @return Item[]
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->items);
    }

    public function apply(callable $callback): void
    {
        foreach ($this->items as $item) {
            $callback($item);
        }
    }

    /**
     * @return Item[]
     */
    public function asArray(): array
    {
        return $this->items;
    }
}
