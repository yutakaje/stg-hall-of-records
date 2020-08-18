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

/**
 * @extends AbstractRepository<Game>
 */
final class GameRepository extends AbstractRepository implements GameRepositoryInterface
{
    /** @var Game[] */
    private array $games;

    public function __construct()
    {
        $this->games = [];
    }

    /**
     * @param array<string,mixed> $sort
     */
    public function all(array $sort = []): Games
    {
        return new Games(
            $this->sortItems($this->games, $sort)
        );
    }

    public function add(Game $game): void
    {
        $this->games[$game->id()] = $game;
    }
}
