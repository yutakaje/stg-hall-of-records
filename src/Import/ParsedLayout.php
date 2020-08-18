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
    /** @var array<string,string> */
    private array $templates;

    /**
     * @param ParsedColumn[] $columns;
     * @param array<string,mixed> $sort
     * @param array<string,string> $templates
     */
    public function __construct(
        array $columns,
        array $sort,
        array $templates
    ) {
        $this->columns = $columns;
        $this->sort = $sort;
        $this->templates = $templates;
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

    /**
     * @return array<string,string>
     */
    public function templates(): array
    {
        return $this->templates;
    }
}
