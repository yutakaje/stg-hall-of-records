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

final class ParsedLayout
{
    /** @var ParsedColumn[] */
    private array $columns;
    /** @var array<string,mixed> */
    private array $sort;

    /**
     * @param ParsedColumn[] $columns;
     * @param array<string,mixed> $sort
     */
    public function __construct(
        array $columns,
        array $sort
    ) {
        $this->columns = $columns;
        $this->sort = $sort;
    }

    /**
     * @return ParsedColumn[]
     */
    public function columns(): array
    {
        return $this->columns;
    }

    /**
     * @return array<string,mixed>
     */
    public function sort(): array
    {
        return $this->sort;
    }
}
