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
use Stg\HallOfRecords\Export\MediaWiki\GameVariable;
use Stg\HallOfRecords\Export\MediaWiki\Layout;
use Stg\HallOfRecords\Export\MediaWiki\MainTemplate;
use Stg\HallOfRecords\Export\MediaWiki\ScoreVariable;

final class MediaWikiExporter
{
    private SettingRepositoryInterface $settings;
    private GameRepositoryInterface $games;
    private ScoreRepositoryInterface $scores;
    private TwigFactory $twigFactory;

    public function __construct(
        SettingRepositoryInterface $settings,
        GameRepositoryInterface $games,
        ScoreRepositoryInterface $scores
    ) {
        $this->settings = $settings;
        $this->games = $games;
        $this->scores = $scores;
        $this->twigFactory = new TwigFactory();
    }

    public function export(): string
    {
        $globalSettings = $this->settings->filterGlobal();
        $globalLayout = Layout::createFromArray(
            $globalSettings->get('layout', [])
        );

        return $this->createMainTemplate($globalLayout)->render([
            'description' => $globalSettings->get('description', ''),
            'games' => $this->createGameVariables($globalLayout),
        ]);
    }

    private function createMainTemplate(Layout $globalLayout): MainTemplate
    {
        return new MainTemplate(
            $this->twigFactory->create($globalLayout->templates())
        );
    }

    /**
     * @return \stdClass[]
     */
    private function createGameVariables(Layout $globalLayout): array
    {
        return $this->games->all()
            ->sort($globalLayout->sort('games'))
            ->map(
                fn (Game $game) => $this->createGameVariable($game, $globalLayout)
            );
    }

    private function createGameVariable(
        Game $game,
        Layout $globalLayout
    ): GameVariable {
        $settings = $this->settings->filterByGame($game->id());
        $layout = Layout::createFromArray(
            $settings->get('layout', [])
        );

        return new GameVariable(
            $game,
            $layout,
            $this->createScoreVariables($game, $layout, $globalLayout)
        );
    }

    /**
     * @return ScoreVariable[]
     */
    private function createScoreVariables(
        Game $game,
        Layout $layout,
        Layout $globalLayout
    ): array {
        // Group scores by distinct features and take the top X
        // entries out of each group.
        return $this->scores->filterByGame($game->id())
            ->top(array_merge(
                $layout->group('scores'),
                $globalLayout->group('scores')
            ))->sort(array_merge(
                $layout->sort('scores'),
                $globalLayout->sort('scores')
            ))->map(
                fn (Score $score) => $this->createScoreVariable($score, $layout)
            );
    }

    /**
     * @return ScoreVariable
     */
    private function createScoreVariable(
        Score $score,
        Layout $layout
    ): ScoreVariable {
        return new ScoreVariable(
            $score,
            $layout,
            $this->twigFactory
        );
    }
}
