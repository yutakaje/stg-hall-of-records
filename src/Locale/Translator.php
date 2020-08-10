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

namespace Stg\HallOfRecords\Locale;

final class Translator
{
    /**
     * Translations are grouped for better access.
     *
     * @var array<string,array<string,string|string[]>>
     */
    private array $translations;

    private ?Translator $fallbackTranslator;

    public function __construct(?Translator $fallbackTranslator = null)
    {
        $this->translations = [];
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

        throw new \InvalidArgumentException(
            'Value and its translation must both have the same data type,'
            .  ' which is either a string or an array of strings.'
        );
    }

    public function translate(string $property, string $value): string
    {
        $index = $this->indexFor($value);

        if (isset($this->translations[$property][$index])) {
            if (!is_string($this->translations[$property][$index])) {
                throw new \InvalidArgumentException(
                    "Invalid data type, property `{$property}` should"
                    . ' be translated into a string.'
                );
            }

            return $this->translations[$property][$index];
        } elseif ($this->fallbackTranslator !== null) {
            return $this->fallbackTranslator->translate($property, $value);
        } else {
            return $value;
        }
    }

    /**
     * @param string[] $values
     * @return string[]
     */
    public function translateArray(string $property, array $values): array
    {
        $index = $this->indexFor($values);

        if (isset($this->translations[$property][$index])) {
            if (!is_array($this->translations[$property][$index])) {
                throw new \InvalidArgumentException(
                    "Invalid data type, property `{$property}` should"
                    . ' be translated into an array of strings.'
                );
            }

            return $this->translations[$property][$index];
        } elseif ($this->fallbackTranslator !== null) {
            return $this->fallbackTranslator->translateArray($property, $values);
        } else {
            return $values;
        }
    }

    /**
     * @param string|string[] $value
     */
    private function indexFor($value): string
    {
        if (is_array($value)) {
            $value = implode(';', $value);
        }

        return md5($value);
    }
}
