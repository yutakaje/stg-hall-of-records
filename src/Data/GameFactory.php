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

final class GameFactory
{
    public function create(string $name, string $company, Scores $scores): Game
    {
        return new Game(
            $name,
            $company,
            $scores
        );
    }
}
