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

namespace Stg\HallOfRecords\Data;

final class Properties
{
    private string $locale;
    /** @var array<string,mixed> */
    private array $properties;

    /**
     * @param array<string,mixed> $properties
     */
    public function __construct(array $properties = [], string $locale = '')
    {
        $this->properties = $properties;
        $this->locale = $locale;
    }

    public function localizeValue(string $property, string $value): ?string
    {
        foreach ($this->properties['locale'] ?? [] as $entry) {
            if (!is_array($entry)) {
                throw new \UnexpectedValueException(
                    'Property `locale` should be an array of arrays.'
                );
            }

            if (
                $entry['property'] === $property
                && $entry['value'] === $value
                && isset($entry["value-{$this->locale}"])
            ) {
                return $entry["value-{$this->locale}"];
            }
        }

        return null;
    }
}
