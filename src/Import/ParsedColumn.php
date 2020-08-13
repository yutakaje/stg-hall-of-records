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

final class ParsedColumn
{
    private string $label;
    private string $value;
    private bool $groupSameValues;

    public function __construct(
        string $label,
        string $value,
        bool $groupSameValues
    ) {
        $this->label = $label;
        $this->value = $value;
        $this->groupSameValues = $groupSameValues;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function groupSameValues(): bool
    {
        return $this->groupSameValues;
    }
}
