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

final class ScoreFactory
{
    /**
     * @param string[] $comments
     */
    public function create(
        string $player,
        string $score,
        string $ship,
        string $mode,
        string $weapon,
        string $scoredDate,
        string $source,
        array $comments
    ): Score {
        return new Score(
            $player,
            $score,
            $ship,
            $mode,
            $weapon,
            $scoredDate,
            $source,
            $comments
        );
    }
}
