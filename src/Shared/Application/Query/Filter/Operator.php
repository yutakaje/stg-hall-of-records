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

/**
 * @phpstan-type Value self::OP_*
 */
final class Operator
{
    public const OP_EQ = 'eq';
    public const OP_NEQ = 'neq';
    public const OP_LIKE = 'like';
    public const OP_NLIKE = 'nlike';
    public const OP_GT = 'gt';
    public const OP_GTE = 'gte';
    public const OP_LT = 'lt';
    public const OP_LTE = 'lte';

    /** @var Value */
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $this->validate($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * @return Value
     */
    private function validate(string $value)
    {
        if (
            $value === self::OP_EQ
            || $value === self::OP_NEQ
            || $value === self::OP_LIKE
            || $value === self::OP_NLIKE
            || $value === self::OP_GT
            || $value === self::OP_GTE
            || $value === self::OP_LT
            || $value === self::OP_LTE
        ) {
            return $value;
        }

        throw FilterException::invalidOperator($value);
    }
}
