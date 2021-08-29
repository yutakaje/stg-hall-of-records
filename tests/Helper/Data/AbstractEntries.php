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

namespace Tests\Helper\Data;

/**
 * @template T of AbstractEntry
 */
abstract class AbstractEntries
{
    /** @var T[] */
    private array $entries;

    /**
     * @param T[] $entries The entries are expected to be sorted.
     */
    public function __construct($entries = [])
    {
        $this->entries = $entries;
    }

    /**
     * @return T
     */
    public function entryAt(int $index)
    {
        return $this->entries[$index];
    }

    /**
     * @return T[]
     */
    public function entries(): array
    {
        // We shuffle to avoid any unexpected dependency on its initial sorting.
        $entries = $this->entries;
        shuffle($entries);
        return $entries;
    }

    public function numEntries(): int
    {
        return sizeof($this->entries);
    }

    /**
     * @return T[]
     */
    public function sorted(): array
    {
        return $this->entries;
    }
}
