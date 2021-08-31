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

namespace Stg\HallOfRecords\Database\Definition;

use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;

abstract class AbstractTable
{
    private Locales $locales;

    public function __construct(Locales $locales)
    {
        $this->locales = $locales;
    }

    final protected function locales(): Locales
    {
        return $this->locales;
    }

    /**
     * @template T
     * @param array<string,T> $values
     * @return array<string,T>
     */
    final protected function localizeValues(array $values): array
    {
        $localized = [];

        foreach ($this->locales->all() as $locale) {
            $localized[$locale->value()] = $values[$locale->value()];
        }

        return $localized;
    }

    /**
     * @template T
     * @param T $emptyValue
     * @return array<string,T>
     */
    final protected function emptyLocalizedValues($emptyValue): array
    {
        $localized = [];

        foreach ($this->locales->all() as $locale) {
            $localized[$locale->value()] = $emptyValue;
        }

        return $localized;
    }
}
