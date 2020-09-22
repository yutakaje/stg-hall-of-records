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

namespace Stg\HallOfRecords\Export\MediaWiki;

use Stg\HallOfRecords\Data\Game\Game;
use Stg\HallOfRecords\Data\Setting\Settings;

final class EmptyColumnRemover
{
    /** @var ScoreVariable[] */
    private array $scores;

    /**
     * @param ScoreVariable[] $scores
     */
    public function __construct(array $scores)
    {
        $this->scores = $scores;
    }

    /**
     * @return ScoreVariable[]
     */
    public function scores(): array
    {
        return $this->scores;
    }

    /**
     * @param array<string,mixed>[] $columns
     * @return array<string,mixed>[]
     */
    public function remove(array $columns): array
    {
        $indexes = array_values(array_filter(
            array_keys($columns),
            fn (int $index) => $this->isEmpty($index)
        ));

        return $this->removeColumns($columns, $indexes);
    }

    private function isEmpty(int $index): bool
    {
        foreach ($this->scores as $score) {
            $content = trim($score->columns[$index]->value ?? '');
            if ($content !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string,mixed>[] $columns
     * @param int[] $indexes
     * @return array<string,mixed>[]
     */
    private function removeColumns(array $columns, array $indexes): array
    {
        foreach ($this->scores as $score) {
            $score->columns = $this->filterEntriesByIndex(
                $score->columns,
                $indexes
            );
        }

        return $this->filterEntriesByIndex($columns, $indexes);
    }

    /**
     * @param mixed[] $entries
     * @param int[] $indexes
     * @return mixed[]
     */
    private function filterEntriesByIndex(array $entries, array $indexes): array
    {
        return array_values(array_filter(
            $entries,
            fn (int $index) => array_search($index, $indexes, true) === false,
            ARRAY_FILTER_USE_KEY
        ));
    }
}
