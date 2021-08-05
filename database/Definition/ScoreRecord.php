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

final class ScoreRecord extends AbstractRecord
{
    private int $gameId;
    private int $playerId;
    private string $playerName;
    private string $scoreValue;

    public function __construct(
        int $gameId,
        int $playerId,
        string $playerName,
        string $scoreValue
    ) {
        parent::__construct();
        $this->gameId = $gameId;
        $this->playerId = $playerId;
        $this->playerName = $playerName;
        $this->scoreValue = $scoreValue;
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
