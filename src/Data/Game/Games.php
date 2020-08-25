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

use Stg\HallOfRecords\Data\Collection;
use Stg\HallOfRecords\Data\Sorting\ArraySorter;

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
}
