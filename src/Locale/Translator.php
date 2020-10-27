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

namespace Stg\HallOfRecords\Locale;

use Stg\HallOfRecords\Error\StgException;

final class Translator
{
    /**
     * Translations are grouped by property for faster access.
     *
     * @var array<string,array<string,string|string[]>>
     */
    private array $translations;
    /**
     * Regular expressions for fuzzy matching, grouped by property for faster access.
     *
     * @var array<string,array<string,string>>
     */
    private array $fuzzyTranslations;
    private ?Translator $fallbackTranslator;

    public function __construct(?Translator $fallbackTranslator = null)
    {
        $this->translations = [];
        $this->fuzzyTranslations = [];
        $this->fallbackTranslator = $fallbackTranslator;
    }

    /**
     * @param string|string[] $value
     * @param string|string[] $translated
     */
    public function add(string $property, $value, $translated): self
    {
        if (
            is_string($value) && is_string($translated)
            || is_array($value) && is_array($translated)
        ) {
            $this->translations[$property][$this->indexFor($value)] = $translated;
            return $this;
        }

        throw new StgException(
            'Error translating value: Value and its translation must both have the'
            . ' same data type, which is either a string or an array of strings.'
        );
    }

    public function addFuzzy(string $property, string $pattern, string $translated): self
    {
        $pattern = "/{$pattern}/u";
        $this->fuzzyTranslations[$property][$pattern] = $translated;
        return $this;
    }

    /**
     * @param string|string[] $value
     * @return string|string[]
     */
    public function translate(string $property, $value)
    {
        $index = $this->indexFor($value);

        if (isset($this->translations[$property][$index])) {
            return $this->translations[$property][$index];
        } elseif (is_string($value) && $this->hasFuzzyMatch($property, $value)) {
            return $this->translateFuzzy($property, $value);
        } elseif ($this->fallbackTranslator !== null) {
            return $this->fallbackTranslator->translate($property, $value);
        } else {
            return $value;
        }
    }

    private function hasFuzzyMatch(string $property, string $value): bool
    {
        $candidates = $this->fuzzyTranslations[$property] ?? [];

        foreach ($candidates as $pattern => $replace) {
            if (preg_match($pattern, $value) === 1) {
                return true;
            }
        }

        return false;
    }

    private function translateFuzzy(string $property, string $value): string
    {
        $candidates = $this->fuzzyTranslations[$property] ?? [];

        foreach ($candidates as $pattern => $replace) {
            $callback = $this->createFuzzyReplaceCallback($replace);
            $newValue = preg_replace_callback($pattern, $callback, $value);

            if ($newValue !== null) {
                $value = $newValue;
            }
        }

        return $value;
    }

    private function createFuzzyReplaceCallback(string $replace): \Closure
    {
        preg_match_all('/{{(?:[\w-]+)}}/u', $replace, $placeholderMatches);

        $placeholders = array_map(
            fn (string $placeholder) => trim($placeholder, '{}'),
            $placeholderMatches[0]
        );

        return function (array $match) use ($replace, $placeholders): string {
            $value = $replace;

            foreach ($placeholders as $placeholder) {
                $value = str_replace(
                    "{{{$placeholder}}}",
                    $match[$placeholder] ?? '',
                    $value
                );
            }

            return $value;
        };
    }

    /**
     * @param string|string[] $value
     */
    private function indexFor($value): string
    {
        $json = json_encode($value);
        if ($json === false) {
            throw new StgException(
                'Error translating value: Value could not be converted into JSON'
            );
        }

        return md5($json);
    }
}
