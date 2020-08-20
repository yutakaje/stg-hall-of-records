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

namespace Stg\HallOfRecords\Export;

use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\ScoreRepositoryInterface;
use Stg\HallOfRecords\Import\ParsedColumn;
use Stg\HallOfRecords\Import\ParsedLayout;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class MediaWikiExporter
{
    private GameRepositoryInterface $games;
    private ScoreRepositoryInterface $scores;

    public function __construct(
        GameRepositoryInterface $games,
        ScoreRepositoryInterface $scores
    ) {
        $this->games = $games;
        $this->scores = $scores;
    }

    /**
     * @param array<int,ParsedLayout> $layouts
     * @param array<string,string> $globalTemplates
     */
    public function export(array $layouts, array $globalTemplates): string
    {
        return $this->exportGames($layouts, $globalTemplates);
    }

    /**
     * @param array<int,ParsedLayout> $layouts
     * @param array<string,string> $globalTemplates
     */
    private function exportGames(array $layouts, array $globalTemplates): string
    {
        $twig = new Environment(
            new ArrayLoader($globalTemplates)
        );

        return $twig->render('games', [
            'games' => $this->games->all()->map(
                fn (Game $game) => $this->createGameVariable(
                    $game,
                    $layouts[$game->id()]
                )
            ),
        ]);
    }

    private function createGameVariable(
        Game $game,
        ParsedLayout $layout
    ): \stdClass {
        $scores = $this->scores->filterByGame($game);

        $variable = new \stdClass();
        $variable->properties = $game;
        $variable->headers = array_map(
            fn (ParsedColumn $column) => $column->label(),
            $layout->columns()
        );
        $variable->scores = $scores->map(
            fn (Score $score) => $this->createScoreVariable(
                $score,
                $layout->columns()
            )
        );
        $variable->template = $layout->templates()['game'] ?? '';
        return $variable;
    }

    /**
     * @param ParsedColumn[] $columns
     * @return string[]
     */
    private function createScoreVariable(Score $score, array $columns): array
    {
        return array_map(
            fn (ParsedColumn $column) => $this->renderTemplate(
                $column->value(),
                $score
            ),
            $columns
        );
    }

    private function renderTemplate(string $template, Score $score): string
    {
        $renderer = new Environment(new ArrayLoader([
            'template' => $this->preparePlaceholders($template),
        ]));

        return $renderer->render('template', [
            'score' => $score,
        ]);
    }

    private function preparePlaceholders(string $template): string
    {
        return str_replace('{{ ', '{{ score.', $this->replacePattern(
            $template,
            '/{{ ([\w-]+) }}/u',
            fn (array $match) => "{{ {$this->toCamelCase($match[1])} }}"
        ));
    }

    private function toCamelCase(string $propertyName): string
    {
        return $this->replacePattern(
            $propertyName,
            '/-(\w)/',
            fn (array $match) => strtoupper($match[1])
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
