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

namespace Stg\HallOfRecords\Data\Game;

use Stg\HallOfRecords\Data\AbstractRepository;

/**
 * @extends AbstractRepository<Game>
 */
final class GameRepository extends AbstractRepository implements GameRepositoryInterface
{
    /**
     * Games are indexed by id for easier access.
     *
     * @var array<int,Game>
     */
    private array $games;

    public function __construct()
    {
        $this->games = [];
    }

    public function all(): Games
    {
        return new Games(
            array_values($this->games)
        );
    }

    public function add(Game $game): void
    {
        $this->games[$game->id()] = $game;
    }

    public function clear(): void
    {
        $this->games = [];
    }
}
