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

namespace Stg\HallOfRecords\Import;

use Stg\HallOfRecords\Locale\Translator;

final class ParsedScore
{
    private string $player;
    private string $score;
    private string $ship;
    private string $mode;
    private string $weapon;
    private string $scoredDate;
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
}
