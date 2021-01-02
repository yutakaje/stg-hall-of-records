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

use Stg\HallOfRecords\Data\Score\Score;
use Stg\HallOfRecords\Export\Twig;
use Twig\Environment;

final class ScoreVariable extends \stdClass
{
    private Twig $twig;

    /**
     * @param array<string,mixed>[] $columns
     */
    public function __construct(
        Score $score,
        array $columns,
        Twig $twig
    ) {
        $this->twig = $twig;

        $this->properties = $score->properties();
        $this->columns = array_map(
            fn (array $column) => $this->createColumn($column, $score),
            $columns
        );
    }

    /**
     * @param array<string,mixed> $column
     */
    private function createColumn(array $column, Score $score): \stdClass
    {
        $this->twig->addTemplates([
            'current-column' => $column['template'] ?? '',
        ]);

        $variable = new \stdClass();
        $variable->name = $column['name'];
        $variable->value = $this->twig->render('current-column', [
            'score' => $score->properties(),
        ]);
        $variable->attributes = $column['attributes'] ?? [];
        return $variable;
    }

    /**
     * @param array<string,mixed> $column
     */
    private function getColumnAttrs(array $column, Score $score): string
    {
        $columnName = $column['name'] ?? null;
        if ($columnName === null) {
            return '';
        }

        return $score->attribute('layout')['columns'][$columnName] ?? '';
    }
}
