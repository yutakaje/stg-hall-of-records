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
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

/**
 * @phpstan-type Names array<string,string>
 */
final class CompanyEntry extends AbstractEntry
{
    /** @var Names */
    private array $names;
    /** @var Names */
    private array $translitNames;

    private GameEntries $games;

    /**
     * @param Names $names
     * @param Names $translitNames
     */
    public function __construct(array $names, array $translitNames)
    {
        parent::__construct();
        $this->names = $names;
        $this->translitNames = $translitNames;
        $this->games = new GameEntries();
    }

    /**
     * @return Names
     */
    public function names(): array
    {
        return $this->names;
    }

    public function name(Locale $locale): string
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

    public function translitName(Locale $locale): string
    {
        return $this->localizedValue($this->translitNames, $locale);
    }

    public function games(): GameEntries
    {
        return $this->games;
    }

    public function setGames(GameEntries $games): void
    {
        $this->games = $games;
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
