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

use Stg\HallOfRecords\Database\Definition\CompaniesTable;

/**
 * @phpstan-type Names array<string,string>
 */
final class CompanyEntry extends AbstractEntry
{
    /** @var Names */
    private array $names;
    /** @var Names */
    private array $translitNames;

    /** @var GameEntry[] */
    private array $games;

    /**
     * @param Names $names
     * @param Names $translitNames
     */
    public function __construct(array $names, array $translitNames)
    {
        parent::__construct();
        $this->names = $names;
        $this->translitNames = $translitNames;
        $this->games = [];
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
     * @return GameEntry[]
     */
    public function games(): array
    {
        return $this->games;
    }

    public function addGame(GameEntry $game): void
    {
        $this->games[] = $game;
    }

    public function insert(CompaniesTable $db): void
    {
        if ($this->hasId()) {
            return;
        }

        $record = $db->createRecord(
            $this->names,
            $this->translitNames
        );
        $db->insertRecord($record);

        $this->setId($record->id());
    }
}
