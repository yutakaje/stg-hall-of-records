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
     * @param array<string,mixed> $options
     */
    public function createGlobalProperties(
        array $options = []
    ): ParsedGlobalProperties {
        return new ParsedGlobalProperties(
            $options['description'] ?? ''
        );
    }

    /**
     * @param ParsedScore[] $scores
     */
    public function createGame(
        string $name,
        string $company,
        array $scores = []
    ): ParsedGame {
        return new ParsedGame(
            $this->nextGameId++,
            $name,
            $company,
            $scores
        );
    }

    /**
     * @param array<string,mixed> $options
     */
    public function createScore(
        string $player,
        string $score,
        array $options = []
    ): ParsedScore {
        return new ParsedScore(
            $this->nextScoreId++,
            $player,
            $score,
            $options['ship'] ?? '',
            $options['mode'] ?? '',
            $options['weapon'] ?? '',
            $options['scoredDate'] ?? '',
            $options['source'] ?? '',
            $options['comments'] ?? []
        );
    }
}