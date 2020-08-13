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

final class GameFactory
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

    public function create(
        int $id,
        string $name,
        string $company
    ): Game {
        return new Game(
            $id,
            $name,
            $company
        );
    }
}
