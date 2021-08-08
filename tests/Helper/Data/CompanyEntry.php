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
    /** @var GameEntry[] */
    private array $games;

    /**
     * @param Names $names
     */
    public function __construct(array $names)
    {
        parent::__construct();
        $this->names = $names;
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
            $this->names()
        );
        $db->insertRecord($record);

        $this->setId($record->id());
    }
}
