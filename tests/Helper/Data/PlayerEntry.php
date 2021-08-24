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

use Stg\HallOfRecords\Database\Definition\PlayersTable;

/**
 * @phpstan-type Aliases string[]
 */
final class PlayerEntry extends AbstractEntry
{
    private string $name;
    /** @var Aliases */
    private array $aliases;

    private ScoreEntries $scores;

    /**
     * @param Aliases $aliases
     */
    public function __construct(
        string $name,
        array $aliases = []
    ) {
        parent::__construct();
        $this->name = $name;
        $this->aliases = $aliases;
        $this->scores = new ScoreEntries();
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return Aliases
     */
    public function aliases(): array
    {
        return $this->aliases;
    }

    public function scores(): ScoreEntries
    {
        return $this->scores;
    }

    public function setScores(ScoreEntries $scores): void
    {
        $this->scores = $scores;
    }

    public function insert(PlayersTable $db): void
    {
        if ($this->hasId()) {
            return;
        }

        $record = $db->createRecord(
            $this->name,
            $this->aliases
        );
        $db->insertRecord($record);

        $this->setId($record->id());
    }
}
