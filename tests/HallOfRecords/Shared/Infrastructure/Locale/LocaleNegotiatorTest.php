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

namespace Tests\HallOfRecords\Shared\Infrastructure\Locale;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Http\BaseUri;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\LocaleNegotiator;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

class LocaleNegotiatorTest extends \Tests\TestCase
{
    public function testWithNoLocaleSpecified(): void
    {
        $locales = $this->createLocales();

        $negotiator = $this->createNegotiator($locales);

        self::assertSame($locales->get('ja'), $negotiator->negotiate(
            $this->createRequest()
        ));
    }

    public function testWithLocalesInPathWithoutBaseUri(): void
    {
        $this->testWithLocalesInPath('');
    }

    public function testWithLocalesInPathAndBaseUri(): void
    {
        $this->testWithLocalesInPath('/some/path');
    }

    private function testWithLocalesInPath(string $baseUri): void
    {
        $locales = $this->createLocales();

        $negotiator = $this->createNegotiator($locales, new BaseUri($baseUri));

        self::assertSame($locales->get('en'), $negotiator->negotiate(
            $this->createRequest("{$baseUri}/en/uri")
        ));
        self::assertSame($locales->get('kr'), $negotiator->negotiate(
            $this->createRequest("{$baseUri}/kr/uri")
        ));
        self::assertSame($locales->get('ja'), $negotiator->negotiate(
            $this->createRequest("{$baseUri}/fr/uri")
        ));
    }

    public function testWithLocalesInHeader(): void
    {
        $locales = $this->createLocales();

        $negotiator = $this->createNegotiator($locales);

        self::assertSame($locales->get('en'), $negotiator->negotiate(
            $this->createRequest('', 'en-US,en;q=0.5')
        ));
        self::assertSame($locales->get('kr'), $negotiator->negotiate(
            $this->createRequest('', 'kr,ja-JP,es-ES')
        ));
        self::assertSame($locales->get('ja'), $negotiator->negotiate(
            $this->createRequest('', 'fr_FR,it_IT')
        ));
    }

    public function testWithLocalesInPathAndHeader(): void
    {
        $locales = $this->createLocales();

        $negotiator = $this->createNegotiator($locales);

        // Path should have higher priority.
        self::assertSame($locales->get('kr'), $negotiator->negotiate(
            $this->createRequest('kr', 'ja-JP,es-ES')
        ));
        self::assertSame($locales->get('en'), $negotiator->negotiate(
            $this->createRequest('/fr/uri', 'en-US,en;q=0.5')
        ));
        self::assertSame($locales->get('ja'), $negotiator->negotiate(
            $this->createRequest('/fr/uri', 'it_IT')
        ));
    }

    private function createNegotiator(
        Locales $locales,
        ?BaseUri $baseUri = null
    ): LocaleNegotiator {
        return new LocaleNegotiator(
            $locales,
            $baseUri ?? new BaseUri()
        );
    }

    private function createLocales(): Locales
    {
        return new Locales('ja', [
            new Locale('en'),
            new Locale('ja'),
            new Locale('kr'),
        ]);
    }

    private function createRequest(
        string $path = '',
        string $acceptLanguage = ''
    ): ServerRequestInterface {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')
            ->willReturn($path);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')
            ->willReturn($uri);
        $request->method('getHeader')
            ->with('Accept-Language')
            ->willReturn([$acceptLanguage]);

        return $request;
    }
}
