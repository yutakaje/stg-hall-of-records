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

namespace Stg\HallOfRecords\Database\Definition;

final class ScoreRecord
{
    private int $id;
    private int $gameId;
    private int $playerId;
    private string $playerName;
    private string $scoreValue;

    public function __construct(
        int $id,
        int $gameId,
        int $playerId,
        string $playerName,
        string $scoreValue
    ) {
        $this->id = $id;
        $this->gameId = $gameId;
        $this->playerId = $playerId;
        $this->playerName = $playerName;
        $this->scoreValue = $scoreValue;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function gameId(): int
    {
        return $this->gameId;
    }

    public function playerId(): int
    {
        return $this->playerId;
    }

    public function playerName(): string
    {
        return $this->playerName;
    }

    public function scoreValue(): string
    {
        return $this->scoreValue;
    }
}
