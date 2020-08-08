<?php

declare(strict_types=1);

/*
 * This file is part of the stg/hall-of-records package.
 *
 * (c) YTK <yutakaje@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stg\HallOfRecords\Import;

use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\Scores;
use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\Games;
use Stg\HallOfRecords\Data\Properties;

final class YamlParser
{
    private string $locale;
    private ?Properties $globalProperties;
    private ?Games $games;

    public function __construct(string $locale = '')
    {
        $this->locale = $locale;
        $this->globalProperties = null;
        $this->games = null;
    }

    /**
     * @param array<string,mixed>[] $sections
     */
    public function parse(array $sections): void
    {
        $this->globalProperties = new Properties(
            $this->extractGlobalProperties($sections),
            $this->locale
        );

        /** @TODO: Game specific values should be taken into account here */
        $this->games = new Games(array_map(
            fn (array $properties) => $this->parseGame($properties),
            $this->extractGames($sections)
        ));
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function parseGame(array $properties): Game
    {
        $localizations = [
            new Properties($properties, $this->locale),
            $this->globalProperties()
        ];

        return new Game(
            $this->localizeValue($properties, 'name', $localizations),
            $this->localizeValue($properties, 'company', $localizations),
            new Scores(array_map(
                fn (array $entry) => new Score(
                    $this->localizeValue($entry, 'player', $localizations),
                    $this->localizeValue($entry, 'score', $localizations),
                    $this->localizeValue($entry, 'ship', $localizations),
                    $this->localizeValue($entry, 'mode', $localizations),
                    $this->localizeValue($entry, 'weapon', $localizations),
                    $this->localizeValue($entry, 'scored-date', $localizations),
                    $this->localizeValue($entry, 'source', $localizations),
                    $this->localizeArrayValue($entry, 'comments', $localizations)
                ),
                $properties['entries'] ?? []
            ))
        );
    }

    public function globalProperties(): Properties
    {
        if ($this->globalProperties === null) {
            throw new \LogicException(
                'Function `parse` must be called before accessing global properties.'
            );
        }

        return $this->globalProperties;
    }

    public function games(): Games
    {
        if ($this->games === null) {
            throw new \LogicException(
                'Function `parse` must be called before accessing games.'
            );
        }

        return $this->games;
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
            throw new \InvalidArgumentException(
                'First section must contain the global properties.'
                . ' Its property `name` must be set to `global`.'
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

    /**
     * @param array<string,mixed> $properties
     * @param Properties[] $localizations
     */
    private function localizeValue(
        array $properties,
        string $name,
        array $localizations
    ): string {
        // If there's no value for the property, there's nothing to localize.
        if (!isset($properties[$name])) {
            return '';
        }

        // Localization on property level.
        if (isset($properties["{$name}-{$this->locale}"])) {
            return $properties["{$name}-{$this->locale}"];
        }

        // Localization on game or global level.
        foreach ($localizations as $localization) {
            $value = $localization->localizeValue($name, $properties[$name]);
            if ($value !== null) {
                return $value;
            }
        }

        // No localization found, return value verbatim.
        return $properties[$name];
    }

    /**
     * @param array<string,mixed> $properties
     * @param Properties[] $localizations
     * @return string[]
     */
    private function localizeArrayValue(
        array $properties,
        string $name,
        array $localizations
    ): array {
        // If there's no value for the property, there's nothing to localize.
        if (!isset($properties[$name])) {
            return [];
        }

        // Localization on property level.
        if (isset($properties["{$name}-{$this->locale}"])) {
            return $properties["{$name}-{$this->locale}"];
        }

        // Localization on game or global level is not available for comments.

        // No localization found, return value verbatim.
        return $properties[$name];
    }
}
