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

namespace Stg\HallOfRecords\Parser;

use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\Scores;
use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\Games;
use Stg\HallOfRecords\Data\GlobalProperties;

final class YamlParser
{
    private string $locale;
    private ?GlobalProperties $globalProperties;
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
        $this->globalProperties = new GlobalProperties(
            $this->extractGlobalProperties($sections),
            $this->locale
        );

        $this->games = new Games(array_map(
            fn (array $properties) => new Game(
                $this->localizeValue($properties, 'name'),
                $this->localizeValue($properties, 'company'),
                new Scores(array_map(
                    fn (array $entry) => new Score(
                        $this->localizeValue($entry, 'player'),
                        $this->localizeValue($entry, 'score'),
                        $this->localizeValue($entry, 'mode'),
                        $this->localizeValue($entry, 'character'),
                        $this->localizeValue($entry, 'weapon'),
                        $this->localizeValue($entry, 'stage'),
                        $this->localizeValue($entry, 'date'),
                        $this->localizeValue($entry, 'source'),
                        $this->localizeValue($entry, 'comment')
                    ),
                    $properties['entries'] ?? []
                ))
            ),
            $this->extractGames($sections)
        ));
    }

    public function globalProperties(): GlobalProperties
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
     */
    private function localizeValue(array $properties, string $name): string
    {
        // If there's no value for the property, there's nothing to localize.
        if (!isset($properties[$name])) {
            return '';
        }

        if (isset($properties["{$name}-{$this->locale}"])) {
            return $properties["{$name}-{$this->locale}"];
        }

        return $this->globalProperties()->localizeValue($name, $properties[$name]);
    }
}
