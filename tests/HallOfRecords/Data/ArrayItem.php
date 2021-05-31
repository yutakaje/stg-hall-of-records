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

namespace Tests\HallOfRecords\Data;

use Stg\HallOfRecords\Data\ItemInterface;

final class ArrayItem implements ItemInterface
{
    private int $id;
    private string $name;
    /** @var array<string,mixed> */
    private array $properties;

    /**
     * @param array<string,mixed> $properties
     */
    public function __construct(
        int $id,
        string $name,
        array $properties
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->properties = $properties;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function property(string $name)
    {
        switch ($name) {
            case 'id':
                return $this->id;
            case 'name':
                return $this->name;
            default:
                return $this->properties[$name] ?? null;
        }
    }
}
