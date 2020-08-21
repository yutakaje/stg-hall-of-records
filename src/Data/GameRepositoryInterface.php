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

interface GameRepositoryInterface
{
    /**
     * @param array<string,mixed> $sort
     */
    public function all(array $sort = []): Games;

    public function add(Game $game): void;

    public function clear(): void;
}
