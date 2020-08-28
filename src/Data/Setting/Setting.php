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

namespace Stg\HallOfRecords\Data\Setting;

use Stg\HallOfRecords\Data\ItemInterface;

abstract class Setting implements ItemInterface
{
    private string $name;
    /** @var mixed */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @return array<string,mixed>
     */
    public function additionalProperties(): array
    {
        return [];
    }

    /**
     * @return mixed
     */
    public function property(string $name)
    {
        switch ($name) {
            case 'name':
                return $this->name();
            case 'value':
                return $this->value();
            case 'additional-properties':
                return $this->additionalProperties();
            default:
                return null;
        }
    }
}
