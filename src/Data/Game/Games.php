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

namespace Stg\HallOfRecords\Data\Game;

use Stg\HallOfRecords\Data\ArrayGrouper;
use Stg\HallOfRecords\Data\ArraySorter;
use Stg\HallOfRecords\Data\Collection;

/**
 * @extends Collection<Game>
 */
final class Games extends Collection
{
    /**
     * @param array<string,mixed> $sort
     */
    public function sort(array $sort): self
    {
        /** @var ArraySorter<Game> */
        $sorter = new ArraySorter();
        return new self(
            $sorter->sort($this->asArray(), $sort)
        );
    }

    /**
     * @param string[] $group
     * @return Games[]
     */
    public function group(array $group): array
    {
        /** @var ArrayGrouper<Game> */
        $grouper = new ArrayGrouper();
        return array_map(
            fn (array $games) => new self($games),
            $grouper->group($this->asArray(), $group)
        );
    }
}
