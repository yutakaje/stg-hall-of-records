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
use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\Scores;
use Stg\HallOfRecords\Database\GameRepository;
use Stg\HallOfRecords\Database\ScoreRepository;
use Stg\HallOfRecords\Import\ParsedColumn;
use Stg\HallOfRecords\Import\ParsedLayout;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;

final class MediaWikiExporter
{
    private GameRepository $gameRepository;
    private ScoreRepository $scoreRepository;
    private Environment $template;

    public function __construct(
        GameRepository $gameRepository,
        ScoreRepository $scoreRepository
    ) {
        $this->gameRepository = $gameRepository;
        $this->scoreRepository = $scoreRepository;
        $this->templates = new Environment(
            new FilesystemLoader(__DIR__ . '/templates'),
            ['debug' => true,]
        );
    }

    /**
     * @param array<int,ParsedLayout> $layouts
     */
    public function export(array $layouts): string
    {
        return $this->exportGames(array_map(
            fn (Game $game) => $this->exportGame(
                $game,
                $this->scoreRepository->filterByGame($game),
                $layouts[$game->id()]->columns()
            ),
            $this->gameRepository->all()->asArray()
        ));
    }

    /**
     * @param string[] $exportedGames
     */
    private function exportGames(array $exportedGames): string
    {
        return implode(PHP_EOL, $exportedGames);
    }

    /**
     * @param ParsedColumn[] $columns
     */
    private function exportGame(
        Game $game,
        Scores $scores,
        array $columns
    ): string {
        return $this->templates->render('game.tpl', [
            'game' => $game,
            'headers' => array_map(
                fn (ParsedColumn $column) => $column->label(),
                $columns
            ),
            'scores' => array_map(
                fn (Score $score) => $this->exportScore($score, $columns),
                $scores->asArray()
            ),
        ]);
    }

    /**
     * @param ParsedColumn[] $columns
     */
    private function exportScore(Score $score, array $columns): string
    {
        return $this->templates->render('score.tpl', [
            'columns' => array_map(
                fn (ParsedColumn $column) => $this->exportScoreColumn(
                    $score,
                    $column
                ),
                $columns
            ),
        ]);
    }

    private function exportScoreColumn(Score $score, ParsedColumn $column): string
    {
        return $this->templates->render('score-column.tpl', [
            'column' => $this->createScoreColumn($score, $column->value()),
        ]);
    }

    private function createScoreColumn(Score $score, string $template): \stdClass
    {
        $column = new \stdClass();
        $column->value = $this->renderTemplate($template, $score);
        return $column;
    }

    private function renderTemplate(string $template, Score $score): string
    {
        //print_r($this->preparePlaceholders($template));
        $renderer = new Environment(new ArrayLoader([
            'template' => $this->preparePlaceholders($template),
        ]), ['debug' => true,]);

        return $renderer->render('template', [
            'score' => $score,
        ]);
    }

    private function preparePlaceholders(string $template): string
    {
        return str_replace('{{ ', '{{ score.', preg_replace_callback(
            '/{{([\w-]+)}}/u',
            fn (array $match) => $this->toCamelCase($match[1]),
            $template
        ));
    }

    /**
     * @return mixed
     */
    private function replacePlaceholders(string $template, Score $score)
    {
        print_r(preg_replace_callback(
            '/{{([\w-]+)}}/u',
            fn (array $match) => $score->getProperty(
                $this->toCamelCase($match[1])
            ),
            $template
        ));
        return preg_replace_callback(
            '/{{([\w-]+)}}/u',
            fn (array $match) => $score->getProperty(
                $this->toCamelCase($match[1])
            ),
            $template
        );
    }

    private function toCamelCase(string $propertyName): string
    {
        return preg_replace_callback(
            '/-(\w)/',
            fn (array $match) => strtoupper($match[1]),
            $propertyName
        );
    }
}
