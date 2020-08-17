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

namespace Stg\HallOfRecords\Database;

use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\ScoreRepositoryInterface;
use Stg\HallOfRecords\Import\ParsedData;
use Stg\HallOfRecords\Import\ParsedGame;
use Stg\HallOfRecords\Import\ParsedScore;

final class ParsedDataWriter
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

    public function write(ParsedData $data): void
    {
        foreach ($data->games() as $game) {
            $this->insertGame($game);
        }
    }

    private function insertGame(ParsedGame $game): void
    {
        $this->games->add(new Game(
            $game->id(),
            $game->name(),
            $game->company()
        ));

        $this->insertScores($game);
    }

    public function insertScores(ParsedGame $game): void
    {
        foreach ($game->scores() as $score) {
            $this->insertScore($game, $score);
        }
    }

    private function insertScore(ParsedGame $game, ParsedScore $score): void
    {
        $this->scores->add(new Score(
            $score->id(),
            $game->id(),
            $score->player(),
            $score->score(),
            $score->ship(),
            $score->mode(),
            $score->weapon(),
            $score->scoredDate(),
            $score->source(),
            $score->comments(),
        ));
    }
}
