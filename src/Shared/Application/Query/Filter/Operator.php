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
 * @phpstan-type Value self::*
 */
final class Operator
{
    public const EQ = 'eq';
    public const NEQ = 'neq';
    public const LIKE = 'like';
    public const NLIKE = 'nlike';
    public const GT = 'gt';
    public const GTE = 'gte';
    public const LT = 'lt';
    public const LTE = 'lte';

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
            $value === self::EQ
            || $value === self::NEQ
            || $value === self::LIKE
            || $value === self::NLIKE
            || $value === self::GT
            || $value === self::GTE
            || $value === self::LT
            || $value === self::LTE
        ) {
            return $value;
        }

        throw FilterException::invalidOperator($value);
    }
}
