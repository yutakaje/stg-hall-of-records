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

final class GameVariable extends \stdClass
{
    /**
     * @param ScoreVariable[] $scores
     */
    public function __construct(
        Game $game,
        Layout $layout,
        Settings $settings,
        array $scores
    ) {
        $columnFinder = new EmptyColumnFinder();
        $emptyColumns = $columnFinder->find($layout->columnOrder(), $scores);

        $columns = $this->getNonEmptyColumns($layout, $emptyColumns);

        $this->properties = $game->properties();
        $this->scores = array_map(
            fn (ScoreVariable $score) => $this->removeEmptyColumns($score, $emptyColumns),
            $scores
        );
        $this->links = $settings->get('links', []);
        $this->headers = array_map(
            fn (array $column) => $column['label'] ?? '',
            $columns
        );
    }

    /**
     * @param string[] $emptyColumns
     * @return array<string,mixed>[]
     */
    private function getNonEmptyColumns(Layout $layout, array $emptyColumns): array
    {
        return array_values(array_filter(
            array_map(
                fn (string $name) => $layout->column($name),
                $layout->columnOrder()
            ),
            fn (array $column) => !in_array($column['name'], $emptyColumns, true)
        ));
    }

    /**
     * @param string[] $emptyColumns
     */
    private function removeEmptyColumns(
        ScoreVariable $score,
        array $emptyColumns
    ): ScoreVariable {
        $score->columns = array_values(array_filter(
            $score->columns,
            fn (\stdClass $column) => array_search(
                $column->name,
                $emptyColumns,
                true
            ) === false
        ));
        return $score;
    }
}
