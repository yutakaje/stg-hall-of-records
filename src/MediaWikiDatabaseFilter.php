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

namespace Stg\HallOfRecords;

use Stg\HallOfRecords\Error\StgException;

final class MediaWikiDatabaseFilter
{
    /**
     * @return string[]
     */
    public function extractAllGames(string $contents): array
    {
        $gamesSection = strpos($contents, '== Games ==');
        if ($gamesSection === false) {
            return [];
        }

        $contents = substr($contents, $gamesSection);

        if (preg_match_all('/=== (.+) ===/u', $contents, $matches) === false) {
            return [];
        }

        return $matches[1];
    }

    public function filter(string $contents, string $game): string
    {
        return implode(PHP_EOL, [
            $this->extractGlobalSettings($contents),
            $this->extractGame($contents, $game),
        ]);
    }

    private function extractGlobalSettings(string $contents): string
    {
        $startPos = strpos($contents, '== Global settings ==');
        if ($startPos === false) {
            throw $this->createException('Global settings not found');
        }

        $result = preg_match(
            '@<nowiki>(.*?)</nowiki>@s',
            substr($contents, $startPos),
            $matches
        );
        if ($result !== 1) {
            throw $this->createException('Unable to extract global settings');
        }

        return $this->removeDescription($matches[0]);
    }

    private function extractGame(string $contents, string $name): string
    {
        $startPos = strpos($contents, "=== {$name} ===");
        if ($startPos === false) {
            throw $this->createException("Game named `{$name}` not found");
        }

        $result = preg_match(
            '@<nowiki>(.*?)</nowiki>@s',
            substr($contents, $startPos),
            $matches
        );
        if ($result !== 1) {
            throw $this->createException("Unable to extract Game named `{$name}");
        }

        return $matches[0];
    }

    private function removeDescription(string $contents): string
    {
        $startPos = strpos($contents, "description: |");
        if ($startPos === false) {
            return $contents;
        }

        $endPos = strpos(substr($contents, $startPos), "layout:");
        if ($endPos === false) {
            return $contents;
        }

        return implode(PHP_EOL, [
            substr($contents, 0, $startPos),
            substr($contents, $startPos + $endPos),
        ]);
    }

    private function createException(string $message): StgException
    {
        return new StgException("Error filtering input: {$message}");
    }
}
