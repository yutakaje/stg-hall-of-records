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

namespace Stg\HallOfRecords\Shared\Template\MediaWiki;

use Stg\HallOfRecords\Shared\Infrastructure\Http\BaseUri;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

final class Routes
{
    private Locales $locales;
    private string $baseUri;
    private string $locale;

    public function __construct(Locales $locales, BaseUri $baseUri)
    {
        $this->locales = $locales;
        $this->baseUri = $this->removeTrailingSlash($baseUri->value());
        $this->locale = '{locale}';
    }

    public function withLocale(Locale $locale): self
    {
        $clone = clone $this;
        $clone->locale = $locale->value();

        return $clone;
    }

    public function index(): string
    {
        return $this->createUri('');
    }

    public function listCompanies(): string
    {
        return $this->createUri('/companies');
    }

    public function viewCompany(string $id = '{id}'): string
    {
        return $this->createUri("/companies/{$id}");
    }

    public function listGames(): string
    {
        return $this->createUri('/games');
    }

    public function viewGame(string $id = '{id}'): string
    {
        return $this->createUri("/games/{$id}");
    }

    public function listPlayers(): string
    {
        return $this->createUri('/players');
    }

    public function viewPlayer(string $id = '{id}'): string
    {
        return $this->createUri("/players/{$id}");
    }

    private function createUri(string $uri): string
    {
        return $this->removeTrailingSlash(
            "{$this->baseUri}/{$this->locale}/" . ltrim($uri, '/')
        );
    }

    private function removeTrailingSlash(string $uri): string
    {
        return rtrim($uri, '/');
    }

    /**
     * @param \Closure(Routes):string $callback
     * @return array<string,string>
     */
    public function forEachLocale(\Closure $callback): array
    {
        $uris = [];

        foreach ($this->locales->all() as $locale) {
            $uris[$locale->value()] = $callback($this->withLocale($locale));
        }

        return $uris;
    }
}
