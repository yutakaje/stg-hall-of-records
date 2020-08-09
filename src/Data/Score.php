<?php

declare(strict_types=1);

/*
 * This file is part of the stg/hall-of-records package.
 *
 * (c) YTK <yutakaje@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stg\HallOfRecords\Data;

final class Score
{
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
        string $player,
        string $score,
        string $ship,
        string $mode,
        string $weapon,
        string $scoredDate,
        string $source,
        array $comments
    ) {
        $this->player = $player;
        $this->score = $score;
        $this->ship = $ship;
        $this->mode = $mode;
        $this->weapon = $weapon;
        $this->scoredDate = $scoredDate;
        $this->source = $source;
        $this->comments = $comments;
    }

    public function player(): string
    {
        return $this->player;
    }

    public function score(): string
    {
        return $this->score;
    }
}
