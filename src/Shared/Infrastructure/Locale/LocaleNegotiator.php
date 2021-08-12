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

use Psr\Http\Message\ServerRequestInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

final class LocaleNegotiator
{
    private Locales $locales;

    public function __construct(Locales $locales)
    {
        $this->locales = $locales;
    }

    public function negotiate(ServerRequestInterface $request): Locale
    {
        $pathLocale = $this->getPathLocale($request);
        if ($this->locales->exists($pathLocale)) {
            return $this->locales->get($pathLocale);
        }

        foreach ($this->getAcceptedLocales($request) as $acceptedLocale) {
            if ($this->locales->exists($acceptedLocale)) {
                return $this->locales->get($acceptedLocale);
            }
        }

        return $this->locales->default();
    }

    private function getPathLocale(ServerRequestInterface $request): string
    {
        $path = explode('/', trim($request->getUri()->getPath(), '/'));
        return $path[0] ?? '';
    }

    /**
     * @return string[]
     */
    private function getAcceptedLocales(ServerRequestInterface $request): array
    {
        return $this->getAcceptedLocalesFromHeader($request);
    }

    /**
     * @return string[]
     */
    private function getAcceptedLocalesFromHeader(
        ServerRequestInterface $request
    ): array {
        $locales = [];

        foreach ($request->getHeader('Accept-Language') as $acceptedLanguage) {
            foreach (explode(',', $acceptedLanguage) as $entry) {
                $locales[] = $this->extractLocale($entry);
            }
        }

        return $locales;
    }

    private function extractLocale(string $acceptedLanguage): string
    {
        return $this->removeRegion(
            $this->removeWeight($acceptedLanguage)
        );
    }

    private function removeRegion(string $acceptedLanguage): string
    {
        list ($language, ) = explode('-', $acceptedLanguage);

        return $language;
    }

    private function removeWeight(string $acceptedLanguage): string
    {
        list ($language, ) = explode(';', $acceptedLanguage);

        return $language;
    }
}
