<?php

declare(strict_types=1);

/*
 * This file is part of the stg/hall-of-records package.
 *
 * (c) YTK <yutakaje@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stg\HallOfRecords\Import;

use Stg\HallOfRecords\Locale\Translator;

final class ParsedGame
{
    private string $name;
    private string $company;
    /** @var ParsedScore[] */
    private array $scores;

    /**
     * @param ParsedScore[] $scores
     */
    public function __construct(
        string $name,
        string $company,
        array $scores
    ) {
        $this->name = $name;
        $this->company = $company;
        $this->scores = $scores;
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
