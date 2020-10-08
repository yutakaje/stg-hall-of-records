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

final class EmptyColumnFinder
{
    /**
     * @param string[] $columns
     * @param ScoreVariable[] $scores
     * @return string[]
     */
    public function find(array $columns, array $scores): array
    {
        return array_values(array_filter(
            $columns,
            fn (string $name) => $this->isEmpty($name, $scores)
        ));
    }

    /**
     * @param ScoreVariable[] $scores
     */
    private function isEmpty(string $columnName, array $scores): bool
    {
        foreach ($scores as $score) {
            foreach ($score->columns as $column) {
                if ($column->name === $columnName) {
                    $content = trim($column->value ?? '');
                    if ($content !== '') {
                        return false;
                    }
                    break;
                }
            }
        }

        return true;
    }
}
