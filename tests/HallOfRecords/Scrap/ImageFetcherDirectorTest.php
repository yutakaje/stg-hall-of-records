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

namespace Tests\HallOfRecords\Scrap;

use GuzzleHttp\Psr7\Response;
use Stg\HallOfRecords\Scrap\ImageFetcherDirector;
use Stg\HallOfRecords\Scrap\ImageFetcherInterface;

class ImageFetcherDirectorTest extends \Tests\TestCase
{
    public function testHandles(): void
    {
        $fetchers = [
            $this->createMock(ImageFetcherInterface::class),
            $this->createMock(ImageFetcherInterface::class),
            $this->createMock(ImageFetcherInterface::class),
        ];

        $fetchers[0]->method('handles')->willReturn(false);
        $fetchers[1]->method('handles')->willReturn(true);
        $fetchers[2]->method('handles')->willReturn(false);

        $this->assertHandles(false, new ImageFetcherDirector([]));
        $this->assertHandles(true, new ImageFetcherDirector([
            $fetchers[0],
            $fetchers[1],
            $fetchers[2],
        ]));
        $this->assertHandles(false, new ImageFetcherDirector([
            $fetchers[0],
            $fetchers[2],
        ]));
    }

    private function assertHandles(
        bool $expected,
        ImageFetcherDirector $director
    ): void {
        $url = 'https://example.org/' . md5(random_bytes(32));

        self::assertSame($expected, $director->handles($url));
    }

    public function testFetch(): void
    {
        $url = 'https://example.org/' . md5(random_bytes(32));
        $response = new Response(200, [], random_bytes(64));

        $fetchers = [
            $this->createMock(ImageFetcherInterface::class),
            $this->createMock(ImageFetcherInterface::class),
            $this->createMock(ImageFetcherInterface::class),
        ];

        $fetchers[0]->method('handles')->willReturn(false);
        $fetchers[1]->method('handles')->willReturn(true);
        $fetchers[1]->method('fetch')->willReturn([$response]);
        $fetchers[2]->method('handles')->willReturn(false);

        $director = new ImageFetcherDirector([
            $fetchers[0],
            $fetchers[1],
            $fetchers[2],
        ]);

        self::assertSame([$response], $director->fetch($url));
    }
}
