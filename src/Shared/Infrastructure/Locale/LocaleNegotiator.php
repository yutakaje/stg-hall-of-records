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
    private string $baseUri;

    public function __construct(Locales $locales, string $baseUri = '')
    {
        $this->locales = $locales;
        $this->baseUri = $baseUri;
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
        $path = substr($request->getUri()->getPath(), strlen($this->baseUri));
        $parts = explode('/', trim($path, '/'));
        return $parts[0] ?? '';
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
