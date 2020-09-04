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
use Stg\HallOfRecords\Export\TwigFactory;
use Twig\Environment;

final class ScoreVariable extends \stdClass
{
    private TwigFactory $twigFactory;

    public function __construct(
        Score $score,
        Layout $layout,
        TwigFactory $twigFactory
    ) {
        $this->twigFactory = $twigFactory;

        $this->columns = array_map(
            fn (array $column) => $this->createColumn($column, $score),
            $layout->columns()
        );
    }

    /**
     * @param array<string,mixed> $column
     */
    private function createColumn(array $column, Score $score): \stdClass
    {
        $variable = new \stdClass();
        $variable->value = $this->renderTemplate(
            $this->createRenderer($column['template'] ?? ''),
            $score
        );
        $variable->attrs = $this->getColumnAttrs($column, $score);
        return $variable;
    }

    /**
     * @param array<string,mixed> $column
     */
    private function getColumnAttrs(array $column, Score $score): string
    {
        $columnId = $column['id'] ?? null;
        if ($columnId === null) {
            return '';
        }

        return $score->attribute('layout')['columns'][$columnId] ?? '';
    }

    private function createRenderer(string $template): Environment
    {
        return $this->twigFactory->create([
            'template' => $this->preparePlaceholders($template),
        ]);
    }

    private function renderTemplate(Environment $twig, Score $score): string
    {
        return $twig->render('template', [
            'score' => $score->properties(),
        ]);
    }

    private function preparePlaceholders(string $template): string
    {
        return $this->replacePattern(
            $template,
            '/((?:{{)|(?:{%)).? ([\w-]+)/u',
            fn (array $match) => "{$match[1]} {$this->preparePlaceholder($match[2])}"
        );
    }

    private function preparePlaceholder(string $name): string
    {
        $placeholder = "attribute(score, '{$name}')";

        if (substr($name, -5) === '-date') {
            $placeholder .= '|formatDate';
        }

        return $placeholder;
    }

    private function replacePattern(
        string $haystack,
        string $pattern,
        \Closure $callback
    ): string {
        $replaced = preg_replace_callback($pattern, $callback, $haystack);

        if ($replaced === null) {
            throw new \UnexpectedValueException(
                "Error replacing pattern `{$pattern}`"
            );
        }

        return $replaced;
    }
}
