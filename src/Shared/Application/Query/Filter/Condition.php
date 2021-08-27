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

namespace Stg\HallOfRecords\Shared\Application\Query\Filter;

final class Condition
{
    private string $id;
    private string $name;
    private Operator $operator;
    /** @var mixed */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct(
        string $name,
        string $operator,
        $value
    ) {
        $this->id = $this->generateId();
        $this->name = $name;
        $this->operator = new Operator($operator);
        $this->value = $this->normalizeValue($value);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function operator(): Operator
    {
        return $this->operator;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    private function generateId(): string
    {
        return md5(random_bytes(64));
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function normalizeValue($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        if (strpos($value, '"') === 0 && substr($value, -1) === '"') {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
