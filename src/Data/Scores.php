<?php

declare(strict_types=1);

/*
 * This file is part of the stg/hall-of-records package.
 *
 * (c) YTK <yutakaje@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stg\HallOfRecords\Data;

final class Scores
{
    /** @var \ArrayIterator<int,Score> */
    private \ArrayIterator $scores;

    /**
     * @param Score[] $scores
     */
    public function __construct(array $scores)
    {
        $this->scores = new \ArrayIterator($scores);
    }

    /**
     * @return \ArrayIterator<int,Score>
     */
    public function iterator(): \ArrayIterator
    {
        return $this->scores;
    }
}
