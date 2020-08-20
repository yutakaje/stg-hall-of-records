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

final class ParsedDataFactory
{
    private int $nextGameId;
    private int $nextScoreId;

    public function __construct()
    {
        $this->nextGameId = 1;
        $this->nextScoreId = 1;
    }

    /**
     * @param ParsedGame[] $games
     */
    public function create(
        ParsedGlobalProperties $globalProperties,
        array $games
    ): ParsedData {
        return new ParsedData($globalProperties, $games);
    }

    /**
     * @param array<string,mixed> $properties
     */
    public function createGlobalProperties(
        array $properties = []
    ): ParsedGlobalProperties {
        return new ParsedGlobalProperties($properties);
    }

    /**
     * @param array<string,mixed> $properties
     * @param ParsedScore[] $scores
     */
    public function createGame(
        array $properties,
        array $scores,
        ParsedLayout $layout
    ): ParsedGame {
        return new ParsedGame(
            $this->nextGameId++,
            $properties,
            $scores,
            $layout
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    public function createScore(array $properties = []): ParsedScore
    {
        return new ParsedScore(
            $this->nextScoreId++,
            $properties
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    public function createLayout(array $properties = []): ParsedLayout
    {
        /* @TODO Temporary code */
        $columns = $properties['columns'] ?? [];
        unset($properties['columns']);

        return new ParsedLayout($properties, $columns);
    }

    /**
     * @param array<string,mixed> $properties
     */
    public function createColumn(array $properties = []): ParsedColumn
    {
        return new ParsedColumn($properties);
    }
}
