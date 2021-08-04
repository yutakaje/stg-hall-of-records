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
final class GameRecord
{
    private int $id;
    private int $companyId;
    /** @var Names */
    private array $names;

    /**
     * @param Names $names
     */
    public function __construct(
        int $id,
        int $companyId,
        array $names
    ) {
        $this->id = $id;
        $this->companyId = $companyId;
        $this->names = $names;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function companyId(): int
    {
        return $this->companyId;
    }

    public function name(string $locale): string
    {
        $name = $this->names[$locale] ?? null;

        if ($name === null) {
            throw new \InvalidArgumentException(
                "No name specified for id `{$this->id}` and locale `{$locale}`"
            );
        }

        return $name;
    }
}
