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

final class ParsedLayout extends AbstractParsedObject
{
    /** @var ParsedColumn[] */
    private array $columns;

    /**
     * @param array<string,mixed> $properties
     * @param ParsedColumn[] $columns
     */
    public function __construct(
        array $properties,
        array $columns
    ) {
        parent::__construct($properties);
        $this->columns = $columns;
    }

    /**
     * @return ParsedColumn[]
     */
    public function columns(): array
    {
        return $this->columns;
    }
}
