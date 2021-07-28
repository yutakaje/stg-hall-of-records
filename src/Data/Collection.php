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
abstract class Collection
{
    /** @var Item[] */
    private array $items;

    /**
     * @param Item[] $items
     */
    final public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @template T of mixed
     * @param \Closure(Item):T $callback
     * @return T[]
     */
    public function map(\Closure $callback): array
    {
        return array_map($callback, $this->items);
    }

    /**
     * @param \Closure(Item):void $callback
     */
    public function apply(\Closure $callback): void
    {
        foreach ($this->items as $item) {
            $callback($item);
        }
    }

    /**
     * @template T of mixed
     * @param \Closure(T,Item):T $callback
     * @param T $initial
     * @return T
     */
    public function reduce(\Closure $callback, $initial)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * @param \Closure(Item):bool $callback
     * @return static
     */
    public function filter(\Closure $callback): Collection
    {
        return new static(array_values(
            array_filter($this->items, $callback)
        ));
    }

    /**
     * @param Collection<Item> $collection
     * @return static
     */
    public function merge(Collection $collection): self
    {
        return new static(array_merge(
            $this->items,
            $collection->items
        ));
    }

    /**
     * @param array<string,mixed> $sort
     * @return static
     */
    public function sort(array $sort): Collection
    {
        $sorter = new ArraySorter();
        return new static(
            $sorter->sort($this->items, $sort)
        );
    }

    /**
     * @param string[] $group
     * @return static[]
     */
    public function group(array $group): array
    {
        /** @var ArrayGrouper<Item> */
        $grouper = new ArrayGrouper();
        return array_map(
            fn (array $items) => new static($items),
            $grouper->group($this->items, $group)
        );
    }

    /**
     * @return Item[]
     */
    public function asArray(): array
    {
        return $this->items;
    }
}
