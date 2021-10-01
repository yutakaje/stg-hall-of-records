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

namespace Tests\Helper\Data;

use Stg\HallOfRecords\Database\Definition\ScoresTable;

final class ScoreEntry extends AbstractEntry
{
    private GameEntry $game;
    private ?PlayerEntry $player;
    private string $playerName;
    private string $scoreValue;

    public function __construct(
        GameEntry $game,
        ?PlayerEntry $player,
        string $playerName,
        string $scoreValue
    ) {
        parent::__construct();
        $this->game = $game;
        $this->player = $player;
        $this->playerName = $playerName;
        $this->scoreValue = $scoreValue;
    }

    public function game(): GameEntry
    {
        return $this->game;
    }

    public function player(): ?PlayerEntry
    {
        return $this->player;
    }

    public function playerName(): string
    {
        return $this->playerName;
    }

    public function scoreValue(): string
    {
        return $this->scoreValue;
    }

    public function insert(ScoresTable $db): void
    {
        if ($this->hasId()) {
            return;
        }

        $record = $db->createRecord(
            $this->game->id(),
            $this->player !== null ? $this->player->id() : null,
            $this->playerName,
            $this->scoreValue
        );
        $db->insertRecord($record);

        $this->setId($record->id());
    }
}
