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

use Stg\HallOfRecords\Data\AbstractRepository;

/**
 * @extends AbstractRepository<Score>
 */
final class ScoreRepository extends AbstractRepository implements ScoreRepositoryInterface
{
    /**
     * Scores are grouped by game for easier access.
     *
     * @var array<int,array<int,Score>>
     */
    private array $scores;

    public function __construct()
    {
        $this->scores = [];
    }

    public function filterByGame(int $gameId): Scores
    {
        return new Scores(array_values(
            $this->scores[$gameId] ?? []
        ));
    }

    public function add(Score $score): void
    {
        $this->scores[$score->gameId()][$score->id()] = $score;
    }

    public function clear(): void
    {
        $this->scores = [];
    }
}
