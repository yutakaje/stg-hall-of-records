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

use Stg\HallOfRecords\Locale\Translator;

final class ParsedGame
{
    private int $id;
    private string $name;
    private string $company;
    /** @var ParsedScore[] */
    private array $scores;

    /**
     * @param ParsedScore[] $scores
     */
    public function __construct(
        int $id,
        string $name,
        string $company,
        array $scores
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->company = $company;
        $this->scores = $scores;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function company(): string
    {
        return $this->company;
    }

    /**
     * @return ParsedScore[]
     */
    public function scores(): array
    {
        return $this->scores;
    }
}
