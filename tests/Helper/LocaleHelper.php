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

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Container\ContainerInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\TranslatorInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

final class LocaleHelper
{
    private Locales $locales;
    private TranslatorInterface $translator;

    public function __construct(
        Locales $locales,
        TranslatorInterface $translator
    ) {
        $this->locales = $locales;
        $this->translator = $translator;
    }

    public static function init(ContainerInterface $container): self
    {
        return new self(
            $container->get(Locales::class),
            $container->get(TranslatorInterface::class)
        );
    }

    /**
     * @return Locale[]
     */
    public function all(): array
    {
        return $this->locales->all();
    }

    public function get(string $value): Locale
    {
        return $this->locales->get($value);
    }

    public function default(): Locale
    {
        return $this->locales->default();
    }

    public function random(): Locale
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
            $localized[$locale->value()] = "{$value}-{$locale}";
        }

        return $localized;
    }

    public function translate(Locale $locale, string $value): string
    {
        $translated = preg_replace_callback(
            '/\{\{ \'([^\']+?)\'\|trans(?:\((.+?)\))? \}\}/',
            fn (array $match) => $this->translator->trans(
                $locale,
                $match[1],
                $this->parseParameters($match[2] ?? '')
            ),
            $value
        );

        return $translated ?? $value;
    }

    /**
     * @return array<string,mixed>
     */
    private function parseParameters(string $params): array
    {
        if ($params === '') {
            return [];
        }

        try {
            return Json::decode(
                str_replace("'", '"', $params),
                Json::FORCE_ARRAY
            );
        } catch (JsonException $exception) {
            throw new \InvalidArgumentException(
                "Parameters for translate function cannot be decoded `{$params}`"
            );
        }
    }
}
