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

namespace Stg\HallOfRecords\Import\MediaWiki;

final class ParsedProperties
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
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return $this->properties[$name] ?? $default;
    }

    /**
     * @return array<string,mixed>
     */
    public function all(): array
    {
        return $this->properties;
    }
}
