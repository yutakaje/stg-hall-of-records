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

use Stg\HallOfRecords\Data\Game\Game;
use Stg\HallOfRecords\Data\Game\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Score\Score;
use Stg\HallOfRecords\Data\Score\ScoreRepositoryInterface;
use Stg\HallOfRecords\Data\Score\Scores;
use Stg\HallOfRecords\Data\Setting\SettingRepositoryInterface;
use Stg\HallOfRecords\Export\MediaWiki\Layout;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class MediaWikiExporter
{
    private SettingRepositoryInterface $settings;
    private GameRepositoryInterface $games;
    private ScoreRepositoryInterface $scores;

    public function __construct(
        SettingRepositoryInterface $settings,
        GameRepositoryInterface $games,
        ScoreRepositoryInterface $scores
    ) {
        $this->settings = $settings;
        $this->games = $games;
        $this->scores = $scores;
    }

    public function export(): string
    {
        return $this->exportGames();
    }

    private function exportGames(): string
    {
        $globalSettings = $this->settings->filterGlobal();
        $globalLayout = Layout::createFromArray(
            $globalSettings->get('layout', [])
        );

        $twig = new Environment(
            new ArrayLoader($globalLayout->templates())
        );

        $games = $this->games->all()->sort(
            $globalLayout->sort('games')
        );

        return $twig->render('games', [
            'games' => $games->map(
                fn (Game $game) => $this->createGameVariable(
                    $game,
                    $globalLayout
                )
            ),
        ]);
    }

    private function createGameVariable(
        Game $game,
        Layout $globalLayout
    ): \stdClass {
        $settings = $this->settings->filterByGame($game->id());
        $layout = Layout::createFromArray(
            $settings->get('layout', [])
        );

        // Group scores by distinct features and take the top X
        // entries out of each group.
        $scores = $this->scores->filterByGame($game->id())
            ->top(array_merge(
                $layout->group('scores'),
                $globalLayout->group('scores')
            ))
            ->sort(array_merge(
                $layout->sort('scores'),
                $globalLayout->sort('scores')
            ));

        $variable = new \stdClass();
        $variable->properties = $game->properties();
        $variable->headers = array_map(
            fn (array $column) => $column['label'] ?? '',
            $layout->columns()
        );
        $variable->scores = $scores->map(
            fn (Score $score) => $this->createScoreVariable(
                $score,
                $layout->columns()
            )
        );
        $variable->template = $layout->template('game');
        return $variable;
    }

    /**
     * @param array[] $columns
     * @return string[]
     */
    private function createScoreVariable(Score $score, array $columns): array
    {
        return array_map(
            fn (array $column) => $this->renderTemplate(
                $column['template'] ?? '',
                $score
            ),
            $columns
        );
    }

    private function renderTemplate(string $template, Score $score): string
    {
        $renderer = new Environment(new ArrayLoader([
            'template' => $this->prepareSimplifiedVariables($template, 'score'),
        ]));

        return $renderer->render('template', [
            'score' => $score->properties(),
        ]);
    }

    private function prepareSimplifiedVariables(
        string $template,
        string $variableName
    ): string {
        return $this->replacePattern(
            $template,
            '/((?:{{)|(?:{%)).? ([\w-]+)/u',
            fn (array $match) => "{$match[1]} attribute({$variableName}, '{$match[2]}')"
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
