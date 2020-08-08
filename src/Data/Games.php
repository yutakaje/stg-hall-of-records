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

final class Games
{
    /** @var Game[] */
    private array $games;

    /**
     * @param Game[] $games
     */
    public function __construct(array $games)
    {
        $this->games = $games;
    }
}
