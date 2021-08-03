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

namespace Tests\Helper;

use Psr\Container\ContainerInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;

final class LocaleHelper
{
    private Locales $locales;

    public function __construct(Locales $locales)
    {
        $this->locales = $locales;
    }

    public static function init(ContainerInterface $container): self
    {
        return new self(
            $container->get(Locales::class)
        );
    }

    /**
     * @return string[]
     */
    public function all(): array
    {
        return $this->locales->all();
    }

    public function default(): string
    {
        return $this->locales->default();
    }

    public function random(): string
    {
        $locales = $this->all();
        return $locales[array_rand($locales)];
    }

    /**
     * @return array<string,string>
     */
    public function localize(string $value): array
    {
        $localized = [];

        foreach ($this->all() as $locale) {
            $localized[$locale] = "{$value}-{$locale}";
        }

        return $localized;
    }
}
