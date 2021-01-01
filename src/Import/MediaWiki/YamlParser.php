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

namespace Stg\HallOfRecords\Import\MediaWiki;

use Stg\HallOfRecords\Error\StgException;

final class YamlParser
{
    /**
     * @param array<string,mixed>[] $sections
     */
    public function parse(array $sections): ParsedProperties
    {
        return new ParsedProperties([
            'global-properties' => $this->parseGlobalProperties(
                $this->extractGlobalProperties($sections)
            ),
            'games' => $this->parseGames(
                $this->extractGames($sections)
            ),
        ]);
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function parseGlobalProperties(array $properties): ParsedProperties
    {
        if (isset($properties['layout'])) {
            $properties['layout'] = $this->parseLayout($properties['layout'] ?? []);
        }

        return new ParsedProperties($properties);
    }

    /**
     * @param array<string,mixed>[] $games
     * @return ParsedProperties[]
     */
    private function parseGames(array $games): array
    {
        return array_map(
            fn (array $properties) => $this->parseGame($properties),
            $games
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function parseGame(array $properties): ParsedProperties
    {
        // `name-kana` ist just an alias for `name-sort-jp`.
        if (isset($properties['name-kana'])) {
            $properties['name-sort-jp'] = $properties['name-kana'];
            unset($properties['name-kana']);
        }

        // Name used for sorting does usually not differ from its original name.
        if (!isset($properties['name-sort'])) {
            $properties['name-sort'] = $properties['name'] ?? '';
        }

        if (isset($properties['scores'])) {
            $properties['scores'] = array_map(
                fn (array $score) => $this->parseScore($score),
                $properties['scores']
            );
        }

        if (isset($properties['layout'])) {
            $properties['layout'] = $this->parseLayout($properties['layout']);
        }

        if (isset($properties['links'])) {
            $properties['links'] = array_map(
                fn (array $link) => new ParsedProperties($link),
                $properties['links']
            );
        }

        return new ParsedProperties($properties);
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function parseScore(array $properties): ParsedProperties
    {
        // If the achieved score is different from the one displayed within the game,
        // the former can be specified in a separate property named `score-real`.
        // The two score values usually only differ for games where the score can be
        // tracked beyond a counterstop.

        if (!isset($properties['score'])) {
            $properties['score'] = '';
        }
        if (!isset($properties['score-real'])) {
            $properties['score-real'] = $properties['score'];
        }

        // Remove separators from score values for sorting, otherwise they will be
        // interpreted as strings.
        $properties['score-sort'] = $this->convertScoreToNumber(
            $properties['score-real']
        );

        if (isset($properties['links'])) {
            $properties['links'] = array_map(
                fn (array $link) => new ParsedProperties($link),
                $properties['links']
            );
        }

        return new ParsedProperties($properties);
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function parseLayout(array $properties): ParsedProperties
    {
        if (isset($properties['columns'])) {
            $properties['columns'] = array_map(
                fn (array $column) => new ParsedProperties($column),
                array_filter(
                    $properties['columns'],
                    fn ($column) => is_array($column)
                )
            );
        }

        return new ParsedProperties($properties);
    }

    /**
     * @param array<string,mixed>[] $sections
     * @return array<string,mixed>
     */
    private function extractGlobalProperties(array $sections): array
    {
        if ($sections == null) {
            return [];
        }

        if (
            !isset($sections[0])
            || !isset($sections[0]['name'])
            || $sections[0]['name'] !== 'global'
        ) {
            throw new StgException(
                'Error analysing data: First section must contain the global'
                . ' properties. Its property `name` must be set to `global`.'
            );
        }

        return $sections[0];
    }

    /**
     * @param array<string,mixed>[] $sections
     * @return array<string,mixed>[]
     */
    private function extractGames(array $sections): array
    {
        return array_slice($sections, 1);
    }

    private function convertScoreToNumber(string $score): string
    {
        return str_replace(',', '', $score);
    }
}
