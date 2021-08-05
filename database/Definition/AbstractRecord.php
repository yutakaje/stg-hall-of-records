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

abstract class AbstractRecord
{
    private ?int $id;

    public function __construct()
    {
        $this->id = null;
    }

    public function hasId(): bool
    {
        return $this->id === null;
    }

    public function id(): int
    {
        if ($this->id === null) {
            throw new \LogicException('Id has not been set');
        }

        return $this->id;
    }

    public function setId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('Id has already been set');
        }

        $this->id = $id;
    }
}
