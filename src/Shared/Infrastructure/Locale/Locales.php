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

final class Locales
{
    /** @var string[] */
    private array $locales;

    /**
     * @param string[] $locales
     */
    public function __construct(array $locales)
    {
        if ($locales == null) {
            throw new \InvalidArgumentException('At least one locale must be given');
        }

        $this->locales = $locales;
    }

    /**
     * @return string[]
     */
    public function all(): array
    {
        return $this->locales;
    }

    public function exists(string $locale): bool
    {
        return in_array($locale, $this->locales, true);
    }

    public function default(): string
    {
        return $this->locales[0];
    }
}
