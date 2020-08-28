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

namespace Stg\HallOfRecords\Data\Score;

use Stg\HallOfRecords\Data\ArrayGrouper;
use Stg\HallOfRecords\Data\ArraySorter;
use Stg\HallOfRecords\Data\Collection;

/**
 * @extends Collection<Score>
 */
final class Scores extends Collection
{
    /**
     * @param array<string,mixed> $sort
     */
    public function sort(array $sort): self
    {
        /** @var ArraySorter<Score> */
        $sorter = new ArraySorter();
        return new self(
            $sorter->sort($this->asArray(), $sort)
        );
    }

    /**
     * @param string[] $group
     * @return Scores[]
     */
    public function group(array $group): array
    {
        /** @var ArrayGrouper<Score> */
        $grouper = new ArrayGrouper();
        return array_map(
            fn (array $scores) => new self($scores),
            $grouper->group($this->asArray(), $group)
        );
    }
}
