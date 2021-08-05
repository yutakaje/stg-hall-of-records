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

abstract class AbstractEntry
{
    private ?int $id;

    public function __construct()
    {
        $this->id = null;
    }

    public function hasId(): bool
    {
        return $this->id !== null;
    }

    public function id(): int
    {
        if ($this->id === null) {
            throw new \LogicException('Id has not been set');
        }

        return $this->id;
    }

    final protected function setId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('Id has already been set');
        }

        $this->id = $id;
    }

    /**
     * @template T
     * @param array<string,T> $values
     * @return T
     */
    protected function localizedValue(array $values, string $locale)
    {
        $value = $values[$locale] ?? null;

        if ($value === null) {
            throw new \InvalidArgumentException(
                "No value specified for id `{$this->id()}` and locale `{$locale}`"
            );
        }

        return $value;
    }
}
