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
    /** @var Score[] */
    private array $scores;

    /**
     * @param Score[] $scores
     */
    public function __construct(array $scores)
    {
        $this->scores = $scores;
    }

    /**
     * @return Score[]
     */
    public function asArray(): array
    {
        return $this->scores;
    }
}
