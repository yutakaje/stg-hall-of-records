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
use Stg\HallOfRecords\Data\Scores;
use Stg\HallOfRecords\Database\GameRepository;
use Stg\HallOfRecords\Database\ScoreRepository;

final class MediaWikiExporter
{
    private GameRepository $gameRepository;
    private ScoreRepository $scoreRepository;

    public function __construct(
        GameRepository $gameRepository,
        ScoreRepository $scoreRepository
    ) {
        $this->gameRepository = $gameRepository;
        $this->scoreRepository = $scoreRepository;
    }

    public function export(): string
    {
        return $this->exportGames(array_map(
            fn (Game $game) => $this->exportGame(
                $game,
                $this->scoreRepository->filterByGame($game)
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

    private function exportGame(Game $game, Scores $scores): string
    {
        return $game->name();
    }
}
