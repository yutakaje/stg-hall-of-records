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
use Stg\HallOfRecords\Data\Scores;
use Stg\HallOfRecords\Import\ParsedColumn;
use Stg\HallOfRecords\Import\ParsedLayout;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;

final class MediaWikiExporter
{
    private GameRepositoryInterface $games;
    private ScoreRepositoryInterface $scores;
    private Environment $twig;

    public function __construct(
        GameRepositoryInterface $games,
        ScoreRepositoryInterface $scores
    ) {
        $this->games = $games;
        $this->scores = $scores;
        $this->twig = new Environment(
            new FilesystemLoader(__DIR__ . '/templates')
        );
    }

    /**
     * @param array<int,ParsedLayout> $layouts
     */
    public function export(array $layouts): string
    {
        return $this->exportGames($layouts);
    }

    /**
     * @param array<int,ParsedLayout> $layouts
     */
    private function exportGames(array $layouts): string
    {
        return $this->twig->render('games.tpl', [
            'games' => array_map(
                fn (Game $game) => $this->createGameVariable(
                    $game,
                    $this->scores->filterByGame($game),
                    $layouts[$game->id()]->columns()
                ),
                $this->games->all()->asArray()
            ),
        ]);
    }

    /**
     * @param ParsedColumn[] $columns
     */
    private function createGameVariable(
        Game $game,
        Scores $scores,
        array $columns
    ): \stdClass {
        $variable = new \stdClass();
        $variable->game = $game;
        $variable->headers = array_map(
            fn (ParsedColumn $column) => $column->label(),
            $columns
        );
        $variable->scores = array_map(
            fn (Score $score) => $this->createScoreVariable(
                $score,
                $columns
            ),
            $scores->asArray()
        );
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
