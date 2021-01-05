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

use Stg\HallOfRecords\Data\Collection;

/**
 * @extends Collection<Score>
 */
final class Scores extends Collection
{
    /**
     * @param string[] $group
     */
    public function markCurrentRecords(array $group, int $numRecords = 1): self
    {
        return array_reduce(
            $this->sort(['score' => 'desc'])
                ->group($group),
            fn (Scores $merged, Scores $grouped) => $merged->merge(
                $this->markCurrentRecordScores($grouped, $numRecords)
            ),
            new Scores()
        );
    }

    private function markCurrentRecordScores(Scores $scores, int $numRecords): Scores
    {
        foreach ($scores->asArray() as $index => $score) {
            if ($index < $numRecords) {
                $score->markAsCurrentRecord();
            }
        }

        return $scores;
    }
}
