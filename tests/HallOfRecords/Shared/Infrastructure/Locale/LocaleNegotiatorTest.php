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
use Stg\HallOfRecords\Shared\Infrastructure\Locale\LocaleNegotiator;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;

class LocaleNegotiatorTest extends \Tests\TestCase
{
    public function testWithLocalesInHeader(): void
    {
        $negotiator = new LocaleNegotiator(new Locales([
            'ja',
            'en',
            'kr',
        ]));

        self::assertSame('en', $negotiator->negotiate(
            $this->createRequest('en-US,en;q=0.5')
        ));
        self::assertSame('kr', $negotiator->negotiate(
            $this->createRequest('kr,ja-JP,es-ES')
        ));
        self::assertSame('ja', $negotiator->negotiate(
            $this->createRequest('fr_FR,it_IT')
        ));
    }

    private function createRequest(string $acceptLanguage): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeader')
            ->with('Accept-Language')
            ->willReturn([$acceptLanguage]);

        return $request;
    }
}
