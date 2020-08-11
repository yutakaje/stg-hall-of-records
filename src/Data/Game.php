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

final class Game
{
    private int $id;
    private string $name;
    private string $company;
    private Scores $scores;

    public function __construct(
        int $id,
        string $name,
        string $company,
        Scores $scores
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->company = $company;
        $this->scores = $scores;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function company(): string
    {
        return $this->company;
    }

    public function scores(): Scores
    {
        return $this->scores;
    }
}
