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
            . ' which is either a string or an array of strings.'
        );
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
        } elseif ($this->fallbackTranslator !== null) {
            return $this->fallbackTranslator->translate($property, $value);
        } else {
            return $value;
        }
    }

    /**
     * @param string|string[] $value
     */
    private function indexFor($value): string
    {
        $json = json_encode($value);
        if ($json === false) {
            throw new \InvalidArgumentException(
                'Value could not be converted into JSON'
            );
        }

        return md5($json);
    }
}
