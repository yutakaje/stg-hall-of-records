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

namespace Stg\HallOfRecords\Import;

abstract class AbstractParsedObject
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
    public function getProperty(string $name)
    {
        return $this->properties[$name] ?? null;
    }
}
