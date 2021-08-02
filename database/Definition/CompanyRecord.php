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

namespace Stg\HallOfRecords\Database\Definition;

/**
 * @phpstan-type Names array<string,string>
 */
final class CompanyRecord
{
    private int $id;
    /** @var Names */
    private array $names;

    /**
     * @param Names $names
     */
    public function __construct(
        int $id,
        array $names
    ) {
        $this->id = $id;
        $this->names = $names;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(string $locale): string
    {
        return $this->names[$locale];
    }
}
