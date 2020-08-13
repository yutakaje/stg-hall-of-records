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

final class ScoreFactory
{
    private int $nextId;

    public function __construct()
    {
        $this->nextId = 1;
    }

    public function nextId(): int
    {
        return $this->nextId++;
    }

    /**
     * @param string[] $comments
     */
    public function create(
        int $id,
        int $gameId,
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
            $id,
            $gameId,
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
