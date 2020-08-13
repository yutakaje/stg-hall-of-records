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
    /**
     * @param ParsedGame[] $games
     */
    public function create(
        ParsedGlobalProperties $globalProperties,
        array $games
    ): ParsedData {
        return new ParsedData($globalProperties, $games);
    }

    public function createGlobalProperties(
        string $description = ''
    ): ParsedGlobalProperties {
        return new ParsedGlobalProperties($description);
    }

    /**
     * @param ParsedScore[] $scores
     */
    public function createGame(
        string $name,
        string $company,
        array $scores
    ): ParsedGame {
        return new ParsedGame(
            $name,
            $company,
            $scores
        );
    }

    /**
     * @param string[] $comments
     */
    public function createScore(
        string $player,
        string $score,
        string $ship,
        string $mode,
        string $weapon,
        string $scoredDate,
        string $source,
        array $comments
    ): ParsedScore {
        return new ParsedScore(
            $player,
            $score,
            $ship,
            $mode,
            $weapon,
            $scoredDate,
            $source,
            $comments
        );
    }
}
