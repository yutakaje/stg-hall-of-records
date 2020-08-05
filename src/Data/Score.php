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
    /** Game mode / Difficulty (e.g. Original, Maniac, Normal, Expert, ...) */
    private string $mode;
    /** Character / Ship */
    private string $character;
    /** Weapon / Style */
    private string $weapon;
    /** Stage reached / Loop information */
    private string $stage;
    /** Format: YYYY-MM-DD|YYYY-MM|YYYY */
    private string $date;
    /** Information source */
    private string $source;
    private string $comment;

    public function __construct(
        string $player,
        string $score,
        string $mode,
        string $character,
        string $weapon,
        string $stage,
        string $date,
        string $source,
        string $comment
    ) {
        $this->player = $player;
        $this->score = $score;
        $this->mode = $mode;
        $this->character = $character;
        $this->weapon = $weapon;
        $this->stage = $stage;
        $this->date = $date;
        $this->source = $source;
        $this->comment = $comment;
    }
}
