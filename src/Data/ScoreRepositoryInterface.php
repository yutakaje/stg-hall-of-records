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

interface ScoreRepositoryInterface
{
    /**
     * @param array<string,mixed> $sort
     */
    public function filterByGame(Game $game, array $sort = []): Scores;

    public function add(Score $score): void;

    public function clear(): void;
}
