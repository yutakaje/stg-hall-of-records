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

namespace Stg\HallOfRecords\Shared\Infrastructure\Locale;

use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

final class Locales
{
    /** @var Locale[] */
    private array $locales;
    private Locale $default;

    /**
     * @param Locale[] $locales
     */
    public function __construct(string $default, array $locales)
    {
        if ($locales == null) {
            throw new \InvalidArgumentException('At least one locale must be given');
        }

        $this->locales = $locales;
        $this->default = $this->get($default);
    }

    /**
     * @return Locale[]
     */
    public function all(): array
    {
        return $this->locales;
    }

    public function get(string $value): Locale
    {
        $locale = $this->getByValue($value);

        if ($locale === null) {
            throw new \LogicException("Locale `{$value}` does not exist");
        }

        return $locale;
    }

    public function exists(string $value): bool
    {
        return $this->getByValue($value) !== null;
    }

    public function default(): Locale
    {
        return $this->default;
    }

    private function getByValue(string $value): ?Locale
    {
        foreach ($this->locales as $locale) {
            if ($locale->value() === $value) {
                return $locale;
            }
        }

        return null;
    }
}
