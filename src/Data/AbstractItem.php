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

abstract class AbstractItem implements ItemInterface
{
    /** @var array<string,mixed> */
    private array $properties;

    /**
     * @param array<string,mixed> $properties
     */
    public function __construct(array $properties = [])
    {
        $this->properties = $properties;
    }

    /**
     * @return mixed
     */
    public function property(string $name)
    {
        return $this->properties()[$name] ?? null;
    }

    /**
     * @return array<string,mixed>
     */
    public function properties(): array
    {
        return $this->properties;
    }

    /**
     * @return mixed
     */
    public function attribute(string $name)
    {
        return $this->property('attributes')[$name] ?? null;
    }
}
