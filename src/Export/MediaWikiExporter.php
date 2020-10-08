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
    private Twig $twig;

    public function __construct(
        SettingRepositoryInterface $settings,
        GameRepositoryInterface $games,
        ScoreRepositoryInterface $scores
    ) {
        $this->settings = $settings;
        $this->games = $games;
        $this->scores = $scores;
        $this->twig = new Twig();
    }

    public function export(string $locale = ''): string
    {
        $globalSettings = $this->settings->filterGlobal();
        $globalLayout = Layout::createFromArray(
            $globalSettings->get('layout', [])
        );

        $this->twig->registerFormatter(new Formatter($locale));
        $this->twig->addTemplates($globalLayout->templates());

        return $this->createMainTemplate($globalLayout)->render([
            'description' => $globalSettings->get('description', ''),
            'games' => $this->createGameVariables($locale, $globalLayout),
        ]);
    }

    private function createMainTemplate(Layout $globalLayout): MainTemplate
    {
        return new MainTemplate($this->twig);
    }

    private function createGameVariables(
        string $locale,
        Layout $globalLayout
    ): \stdClass {
        $games = new \stdClass();
        $games->all = $this->games->all()
            ->sort($globalLayout->sort('games'))
            ->map(fn (Game $game) => $this->createGameVariable(
                $game,
                $globalLayout,
            ));
        $games->grouped = [
            'byInitials' => $this->groupGamesByInitials($games->all, $locale),
        ];
        return $games;
    }

    private function createGameVariable(
        Game $game,
        Layout $globalLayout
    ): GameVariable {
        $settings = $this->settings->filterByGame($game->id());
        $layout = Layout::createFromArray(
            $settings->get('layout', [])
        )->merge($globalLayout);

        if ($layout->template('game') !== '') {
            $this->twig->addTemplates([
                "game-{$game->id()}" => $layout->template('game'),
            ]);
        }

        return new GameVariable(
            $game,
            $layout,
            $settings,
            $this->createScoreVariables($game, $layout)
        );
    }

    /**
     * @return ScoreVariable[]
     */
    private function createScoreVariables(
        Game $game,
        Layout $layout
    ): array {
        $columns = array_map(
            fn (string $name) => $layout->column($name),
            $layout->columnOrder()
        );

        // Group scores by distinct features and take the top X
        // entries out of each group.
        return $this->scores->filterByGame($game->id())
            ->top($layout->group('scores'))
            ->sort($layout->sort('scores'))
            ->map(fn (Score $score) => $this->createScoreVariable(
                $score,
                $columns
            ));
    }

    /**
     * @param array<string,mixed>[] $columns
     */
    private function createScoreVariable(
        Score $score,
        array $columns
    ): ScoreVariable {
        return new ScoreVariable(
            $score,
            $columns,
            $this->twig
        );
    }

    /**
     * @param GameVariable[] $games
     * @return \stdClass[]
     */
    private function groupGamesByInitials(array $games, string $locale): array
    {
        $groups = $this->getInitialGroups($locale);

        foreach ($games as $game) {
            $name = $game->properties['name-sort']
                ?? $game->properties['name'];
            $index = $this->findInitialGroup($groups, $name);
            $groups[$index]->games[] = $game;
        }

        return $groups;
    }

    /**
     * @param \stdClass[] $groups
     */
    private function findInitialGroup(array $groups, string $name): int
    {
        $calcByteSum = fn (string $char) => array_reduce(
            range(0, strlen($char) - 1),
            fn (int $byteSum, int $byte) => $byteSum * 1000 + ord(
                substr($char, $byte, 1)
            ),
            0
        );

        $firstChar = strtolower(mb_substr($name, 0, 1));
        $firstCharByteSum = $calcByteSum($firstChar);

        // We are only interested in negative values since the initial is
        // supposed to match the start of a block of bytes.
        $distances = array_map(
            fn (int $distance) => $distance * -1,
            array_filter(
                array_map(
                    fn (\stdClass $group) => $calcByteSum(
                        strtolower($group->initial)
                    ) - $firstCharByteSum,
                    $groups
                ),
                fn (int $distance) => $distance <= 0
            )
        );

        $minIndex = 0;
        $minDistance = PHP_INT_MAX;
        foreach ($distances as $index => $distance) {
            if ($distance < $minDistance) {
                $minIndex = $index;
                $minDistance = $distance;
            }
        }
        return $minIndex;
    }

    /**
     * @return \stdClass[]
     */
    private function getInitialGroups(string $locale): array
    {
        $createGroup = function (string $initial, string $title): \stdClass {
            $group = new \stdClass();
            $group->initial = $initial;
            $group->title = $title;
            $group->games = [];
            return $group;
        };

        if ($locale === 'jp') {
            return [
                $createGroup('あ', 'あ行'),
                $createGroup('か', 'か行'),
                $createGroup('さ', 'さ行'),
                $createGroup('た', 'た行'),
                $createGroup('な', 'な行'),
                $createGroup('は', 'は行'),
                $createGroup('ま', 'ま行'),
                $createGroup('や', 'や行'),
                $createGroup('ら', 'ら行'),
                $createGroup('わ', 'わ行'),
                $createGroup('0', '#'),
            ];
        } else {
            return array_merge(
                [
                    $createGroup('0', '0-9'),
                ],
                array_map(
                    fn (string $char) => $createGroup($char, $char),
                    str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 1)
                )
            );
        }
    }
}
