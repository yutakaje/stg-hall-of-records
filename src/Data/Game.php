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

namespace Stg\HallOfRecords\Data;

final class Game
{
    private int $id;
    private string $name;
    private string $company;

    public function __construct(
        int $id,
        string $name,
        string $company
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->company = $company;
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
}
