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
    public function __construct(
        Score $score,
        Layout $layout,
        TwigFactory $twigFactory
    ) {
        $this->columns = array_map(
            fn (array $column) => $this->renderTemplate(
                $this->createRenderer(
                    $twigFactory,
                    $column['template'] ?? ''
                ),
                $score
            ),
            $layout->columns()
        );
    }

    private function createRenderer(
        TwigFactory $twigFactory,
        string $template
    ): Environment {
        return $twigFactory->create([
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
            fn (array $match) => "{$match[1]} attribute(score, '{$match[2]}')"
        );
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
