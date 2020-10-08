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

namespace Stg\HallOfRecords\Export\MediaWiki;

final class Layout
{
    /** @var array<string,string> */
    private array $templates;
    /** @var array<string,mixed[]> */
    private array $sort;
    /** @var array<string,string[]> */
    private array $group;
    /** @var array<string,array<string,mixed>> */
    private array $columns;
    /** @var string[] */
    private array $columnOrder;

    /**
     * @param array<string,string> $templates
     * @param array<string,mixed[]> $sort
     * @param array<string,string[]> $group
     * @param array<string,array<string,mixed>> $columns
     * @param string[] $columnOrder
     */
    public function __construct(
        array $templates,
        array $sort,
        array $group,
        array $columns,
        array $columnOrder
    ) {
        $this->templates = $templates;
        $this->sort = $sort;
        $this->group = $group;
        $this->columns = $columns;
        $this->columnOrder = $columnOrder;

        foreach ($this->columns as $name => $properties) {
            $this->columns[$name]['name'] = $name;
        }
    }

    /**
     * @param array<string,mixed> $properties
     */
    public static function createFromArray(array $properties): self
    {
        return new self(
            $properties['templates'] ?? [],
            $properties['sort'] ?? [],
            $properties['group'] ?? [],
            $properties['columns'] ?? [],
            $properties['column-order'] ?? [],
        );
    }

    /**
     * @return array<string,string>
     */
    public function templates(): array
    {
        return $this->templates;
    }

    public function template(string $name): string
    {
        return $this->templates[$name] ?? '';
    }

    /**
     * @return array<string,mixed>
     */
    public function sort(string $name): array
    {
        return $this->sort[$name] ?? [];
    }

    /**
     * @return string[]
     */
    public function group(string $name): array
    {
        return $this->group[$name] ?? [];
    }

    /**
     * @return array<string,mixed>[]
     */
    public function columns(): array
    {
        return array_values($this->columns);
    }

    /**
     * @return array<string,mixed>
     */
    public function column(string $name): array
    {
        if (!isset($this->columns[$name])) {
            throw new \InvalidArgumentException(
                "Column named `{$name}` does not exist"
            );
        }
        return $this->columns[$name];
    }

    /**
     * @return string[]
     */
    public function columnOrder(): array
    {
        return $this->columnOrder;
    }

    public function merge(Layout $layout): self
    {
        return new self(
            $this->templates,
            $this->mergeSort($layout->sort),
            $this->mergeGroup($layout->group),
            $this->mergeColumns($layout->columns),
            $this->mergeColumnOrder($layout->columnOrder)
        );
    }

    /**
     * @param array<string,mixed> $sort
     * @return array<string,mixed>
     */
    private function mergeSort(array $sort): array
    {
        $merged = $this->sort;

        foreach ($sort as $name => $values) {
            $merged[$name] = array_merge(
                $merged[$name] ?? [],
                $values
            );
        }

        return $merged;
    }

    /**
     * @param array<string,string[]> $group
     * @return array<string,string[]>
     */
    private function mergeGroup(array $group): array
    {
        $merged = $this->group;

        foreach ($group as $name => $values) {
            $merged[$name] = array_merge(
                $merged[$name] ?? [],
                $values
            );
        }

        return $merged;
    }

    /**
     * @param array<string,mixed>[] $columns
     * @return array<string,mixed>[]
     */
    private function mergeColumns(array $columns): array
    {
        $merged = $columns;

        foreach ($this->columns as $name => $properties) {
            $merged[$name] = array_merge($merged[$name] ?? [], $properties);
        }

        return $merged;
    }

    /**
     * @param string[] $columnOrder
     * @return string[]
     */
    private function mergeColumnOrder(array $columnOrder): array
    {
        return $this->columnOrder != null ? $this->columnOrder : $columnOrder;
    }
}
