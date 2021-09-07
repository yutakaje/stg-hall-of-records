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

namespace Tests\HallOfRecords\Shared\Template\MediaWiki;

use Stg\HallOfRecords\Shared\Infrastructure\Http\BaseUri;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;

class RoutesTest extends \Tests\TestCase
{
    public function testWithEmptyBaseUri(): void
    {
        $routes = new Routes(new Locales('en', [
            new Locale('en'),
            new Locale('ja'),
            new Locale('kr'),
        ]), new BaseUri());

        self::assertSame('/{locale}', $routes->index());
        self::assertSame('/{locale}/companies', $routes->listCompanies());
        self::assertSame('/{locale}/companies/{id}', $routes->viewCompany());
        self::assertSame('/{locale}/games', $routes->listGames());
        self::assertSame('/{locale}/games/{id}', $routes->viewGame());
        self::assertSame('/{locale}/players', $routes->listPlayers());
        self::assertSame('/{locale}/players/{id}', $routes->viewPlayer());
        self::assertSame(
            [
                'en' => '/en/companies',
                'ja' => '/ja/companies',
                'kr' => '/kr/companies',
            ],
            $routes->forEachLocale(
                fn ($routes) => $routes->listCompanies()
            )
        );
    }

    public function testWithUrlNoTrailingSlash(): void
    {
        $expected = 'https://stg.example.org/' . md5(random_bytes(8));
        $input = $expected;

        $this->testWithBaseUri($input, $expected);
    }

    public function testWithUrlTrailingSlash(): void
    {
        $expected = 'https://stg.example.org/' . md5(random_bytes(8));
        $input = "{$expected}/";

        $this->testWithBaseUri($input, $expected);
    }

    public function testWithBaseUriNoTrailingSlash(): void
    {
        $expected = '/' . md5(random_bytes(8));
        $input = $expected;

        $this->testWithBaseUri($input, $expected);
    }

    public function testWithBaseUriTrailingSlash(): void
    {
        $expected = '/' . md5(random_bytes(8));
        $input = "{$expected}/";

        $this->testWithBaseUri($input, $expected);
    }

    private function testWithBaseUri(
        string $inputBaseUri,
        string $expectedBaseUri
    ): void {
        $routes = new Routes(new Locales('en', [
            new Locale('en'),
            new Locale('ja'),
            new Locale('kr'),
        ]), new BaseUri($inputBaseUri));

        self::assertSame("{$expectedBaseUri}/{locale}", $routes->index());
        self::assertSame("{$expectedBaseUri}/{locale}/companies", $routes->listCompanies());
        self::assertSame("{$expectedBaseUri}/{locale}/companies/{id}", $routes->viewCompany());
        self::assertSame("{$expectedBaseUri}/{locale}/games", $routes->listGames());
        self::assertSame("{$expectedBaseUri}/{locale}/games/{id}", $routes->viewGame());
        self::assertSame("{$expectedBaseUri}/{locale}/players", $routes->listPlayers());
        self::assertSame("{$expectedBaseUri}/{locale}/players/{id}", $routes->viewPlayer());
        self::assertSame(
            [
                'en' => "{$expectedBaseUri}/en/companies",
                'ja' => "{$expectedBaseUri}/ja/companies",
                'kr' => "{$expectedBaseUri}/kr/companies",
            ],
            $routes->forEachLocale(
                fn ($routes) => $routes->listCompanies()
            )
        );
    }
}
