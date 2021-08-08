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

namespace Tests\Helper\Data;

use Stg\HallOfRecords\Database\Definition\GamesTable;

/**
 * @phpstan-type Names array<string,string>
 */
final class GameEntry extends AbstractEntry
{
    /** @var Names */
    private array $names;
    private CompanyEntry $company;
    /** @var ScoreEntry[] */
    private array $scores;

    /**
     * @param Names $names
     */
    public function __construct(
        array $names,
        CompanyEntry $company
    ) {
        parent::__construct();
        $this->names = $names;
        $this->company = $company;
        $this->scores = [];
    }

    /**
     * @return Names
     */
    public function names(): array
    {
        return $this->names;
    }

    public function name(string $locale): string
    {
        return $this->localizedValue($this->names, $locale);
    }

    public function company(): CompanyEntry
    {
        return $this->company;
    }

    /**
     * @return ScoreEntry[]
     */
    public function scores(): array
    {
        return $this->scores;
    }

    public function addScore(ScoreEntry $score): void
    {
        $this->scores[] = $score;
    }

    public function insert(GamesTable $db): void
    {
        if ($this->hasId()) {
            return;
        }

        $record = $db->createRecord(
            $this->company->id(),
            $this->names
        );
        $db->insertRecord($record);

        $this->setId($record->id());
    }
}
