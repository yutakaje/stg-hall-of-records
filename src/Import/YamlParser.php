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

use Stg\HallOfRecords\Locale\Translator;

final class YamlParser
{
    private ParsedDataFactory $parsedDataFactory;
    private string $locale;

    public function __construct()
    {
        $this->parsedDataFactory = new ParsedDataFactory();
        $this->locale = '';
    }

    /**
     * @param array<string,mixed>[] $sections
     */
    public function parse(array $sections, string $locale = ''): ParsedData
    {
        $this->locale = $locale;
        $globalTranslator = $this->parseTranslations(
            $this->extractGlobalProperties($sections)
        );

        return $this->parsedDataFactory->create(
            $this->parseGlobalProperties(
                $this->extractGlobalProperties($sections),
                $globalTranslator
            ),
            $this->parseGames(
                $this->extractGames($sections),
                $globalTranslator
            )
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function parseGlobalProperties(
        array $properties,
        Translator $translator
    ): ParsedGlobalProperties {
        $translator = $this->parseLocalTranslations($properties, $translator);

        $properties = $this->translateProperties($translator, $properties);

        /* @TODO Temporary code */
        unset(
            $properties['name'],
            $properties['translations'],
        );

        return $this->parsedDataFactory->createGlobalProperties($properties);
    }

    /**
     * @param array<string,mixed>[] $games
     * @return ParsedGame[]
     */
    private function parseGames(array $games, Translator $translator): array
    {
        return array_map(
            fn (array $properties) => $this->parseGame(
                $properties,
                $translator
            ),
            $games
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function parseGame(array $properties, Translator $translator): ParsedGame
    {
        $translator = $this->parseLocalTranslations(
            $properties,
            $this->parseTranslations($properties, $translator),
        );

        $properties = $this->translateProperties($translator, $properties);
        $scores = array_map(
            fn (array $score) => $this->parseScore($score, $translator),
            $properties['scores'] ?? []
        );
        $layout = $this->parseLayout($properties['layout'] ?? [], $translator);

        /* @TODO Temporary code */
        unset(
            $properties['scores'],
            $properties['layout'],
            $properties['translations'],
        );

        return $this->parsedDataFactory->createGame(
            $properties,
            $scores,
            $layout
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function parseScore(
        array $properties,
        Translator $fallbackTranslator
    ): ParsedScore {
        $translator = $this->parseLocalTranslations(
            $properties,
            $fallbackTranslator
        );

        return $this->parsedDataFactory->createScore(
            $this->translateProperties($translator, $properties)
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function parseTranslations(
        array $properties,
        ?Translator $fallbackTranslator = null
    ): Translator {
        return array_reduce(
            array_filter(
                $properties['translations'] ?? [],
                fn ($entry) => is_array($entry)
                    && isset($entry['property'])
                    && isset($entry['value'])
                    && isset($entry["value-{$this->locale}"])
            ),
            fn (Translator $translator, array $entry) => $translator->add(
                $entry['property'],
                $entry['value'],
                $entry["value-{$this->locale}"]
            ),
            new Translator($fallbackTranslator)
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function parseLocalTranslations(
        array $properties,
        Translator $fallbackTranslator
    ): Translator {
        return array_reduce(
            array_filter(
                array_keys($properties),
                fn ($name) => is_string($name)
                    && isset($properties["{$name}-{$this->locale}"])
            ),
            fn (Translator $translator, string $name) => $translator->add(
                $name,
                $properties[$name],
                $properties["{$name}-{$this->locale}"]
            ),
            new Translator($fallbackTranslator)
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function parseLayout(
        array $properties,
        Translator $translator
    ): ParsedLayout {
        $properties['columns'] = $columns = array_map(
            fn (array $column) => $this->parseColumn($column, $translator),
            array_filter(
                $properties['columns'] ?? [],
                fn ($column) => is_array($column)
            )
        );

        return $this->parsedDataFactory->createLayout($properties);
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function parseColumn(
        array $properties,
        Translator $fallbackTranslator
    ): ParsedColumn {
        $translator = $this->parseLocalTranslations($properties, $fallbackTranslator);

        return $this->parsedDataFactory->createColumn(
            $this->translateProperties($translator, $properties)
        );
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
     * @return array<string,mixed>
     */
    private function translateProperties(
        Translator $translator,
        array $properties
    ): array {
        $translated = [];

        $skip = [];
        foreach ($properties as $name => $value) {
            if (isset($skip[$name])) {
                continue;
            }

            if ($value !== null) {
                $translated[$name] = $translator->translate($name, $value);
            } else {
                $translated[$name] = null;
            }

            /* @TODO Temporary code */
            $skip["{$name}-en"] = true;
            $skip["{$name}-jp"] = true;
        }

        return $translated;
    }
}
