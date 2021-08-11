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
    private CompanyEntry $company;
    /** @var Names */
    private array $names;
    /** @var Names */
    private array $translitNames;

    /** @var ScoreEntry[] */
    private array $scores;

    /**
     * @param Names $names
     * @param Names $translitNames
     */
    public function __construct(
        CompanyEntry $company,
        array $names,
        array $translitNames
    ) {
        parent::__construct();
        $this->company = $company;
        $this->names = $names;
        $this->translitNames = $translitNames;
        $this->scores = [];
    }

    public function company(): CompanyEntry
    {
        return $this->company;
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

    /**
     * @return Names
     */
    public function translitNames(): array
    {
        return $this->translitNames;
    }

    public function translitName(string $locale): string
    {
        return $this->localizedValue($this->translitNames, $locale);
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
            $this->names,
            $this->translitNames
        );
        $db->insertRecord($record);

        $this->setId($record->id());
    }
}
