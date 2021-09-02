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

interface ScoreRepositoryInterface
{
    public function all(): Scores;

    public function filterByGame(int $gameId): Scores;

    public function add(Score $score): void;

    public function clear(): void;
}
