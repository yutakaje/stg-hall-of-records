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
 * @extends AbstractRepository<Score>
 */
final class ScoreRepository extends AbstractRepository implements ScoreRepositoryInterface
{
    /**
     * Scores are grouped by game for faster access.
     *
     * @var array<int,Score[]>
     */
    private array $scores;

    public function __construct()
    {
        $this->scores = [];
    }

    /**
     * @param array<string,mixed> $sort
     */
    public function filterByGame(Game $game, array $sort = []): Scores
    {
        return new Scores(
            $this->sortItems($this->scores[$game->id()] ?? [], $sort)
        );
    }

    public function add(Score $score): void
    {
        $this->scores[$score->gameId()][$score->id()] = $score;
    }
}
