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

namespace Stg\HallOfRecords\Data\Game;

use Stg\HallOfRecords\Data\ItemInterface;

final class Game implements ItemInterface
{
    private int $id;
    /** @var array<string,mixed> */
    private array $properties;

    /**
     * @param array<string,mixed> $properties
     */
    public function __construct(
        int $id,
        array $properties = []
    ) {
        $this->id = $id;
        $this->properties = $properties;
    }

    public function id(): int
    {
        return $this->id;
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
        return array_merge($this->properties, [
            'id' => $this->id,
        ]);
    }
}
