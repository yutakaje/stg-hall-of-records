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

namespace Stg\HallOfRecords\Data\Score;

use Stg\HallOfRecords\Data\Sorting\SortableInterface;

final class Score implements SortableInterface
{
    private int $id;
    private int $gameId;
    private string $player;
    private string $score;
    /** Ship / Character  */
    private string $ship;
    /** Game mode / Difficulty (e.g. Original, Maniac, Normal, Expert, ...) */
    private string $mode;
    /** Weapon / Style */
    private string $weapon;
    /** Format: YYYY-MM-DD|YYYY-MM|YYYY */
    private string $scoredDate;
    /** Information source */
    private string $source;
    /** @var string[] */
    private array $comments;

    /**
     * @param string[] $comments
     */
    public function __construct(
        int $id,
        int $gameId,
        string $player,
        string $score,
        string $ship,
        string $mode,
        string $weapon,
        string $scoredDate,
        string $source,
        array $comments
    ) {
        $this->id = $id;
        $this->gameId = $gameId;
        $this->player = $player;
        $this->score = $score;
        $this->ship = $ship;
        $this->mode = $mode;
        $this->weapon = $weapon;
        $this->scoredDate = $scoredDate;
        $this->source = $source;
        $this->comments = $comments;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function gameId(): int
    {
        return $this->gameId;
    }

    public function player(): string
    {
        return $this->player;
    }

    public function score(): string
    {
        return $this->score;
    }

    public function ship(): string
    {
        return $this->ship;
    }

    public function mode(): string
    {
        return $this->mode;
    }

    public function weapon(): string
    {
        return $this->weapon;
    }

    public function scoredDate(): string
    {
        return $this->scoredDate;
    }

    public function source(): string
    {
        return $this->source;
    }

    /**
     * @return string[]
     */
    public function comments(): array
    {
        return $this->comments;
    }

    /**
     * @return mixed
     */
    public function getProperty(string $name)
    {
        return $this->properties()[$name] ?? null;
    }

    /**
     * @return array<string,mixed>
     */
    public function properties(): array
    {
        return [
            'id' => $this->id,
            'game-id' => $this->gameId,
            'player' => $this->player,
            'score' => $this->score,
            'ship' => $this->ship,
            'mode' => $this->mode,
            'weapon' => $this->weapon,
            'scored-date' => $this->scoredDate,
            'source' => $this->source,
            'comments' => $this->comments,
        ];
    }
}