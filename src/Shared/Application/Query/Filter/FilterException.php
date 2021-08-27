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

final class FilterException extends \Exception
{
    public static function invalidOperator(string $operator): self
    {
        return new self("Invalid operator `{$operator}`");
    }

    public static function invalidConjunction(string $operator): self
    {
        return new self("Invalid conjunction `{$operator}");
    }

    public static function invalidFieldName(string $name): self
    {
        return new self("Invalid field name `{$name}`");
    }

    public static function invalidComparison(
        string $type,
        string $operator
    ): self {
        return new self(
            "Invalid operator `{$operator}` for {$type} comparison"
        );
    }
}
