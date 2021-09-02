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

namespace Stg\HallOfRecords\Database\Migration\MediaWiki;

final class Properties
{
    /** @var array<string,mixed> */
    private array $properties;

    /**
     * @param array<string,mixed> $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function consume(string $name, $default = null)
    {
        if (func_num_args() === 2) {
            $value = $this->getPropertyOrDefault($name, $default);
        } else {
            $value = $this->getProperty($name);
        }

        $this->remove($name);

        return $value;
    }

    public function remove(string ...$names): void
    {
        foreach ($names as $name) {
            unset($this->properties[$name]);
        }
    }

    /**
     * @return mixed
     */
    private function getProperty(string $name)
    {
        if (!array_key_exists($name, $this->properties)) {
            throw new \InvalidArgumentException(
                "Invalid property `{$name}`"
            );
        }

        return $this->properties[$name];
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    private function getPropertyOrDefault(string $name, $default)
    {
        if (!array_key_exists($name, $this->properties)) {
            return $default;
        }

        return $this->getProperty($name);
    }

    public function assertEmpty(): void
    {
        if ($this->properties != null) {
            throw new \LogicException(
                'Unhandled properties detected: '
                . implode(', ', array_keys($this->properties))
            );
        }
    }
}
