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
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Stg\HallOfRecords\Http\HttpContentFetcher;
use Stg\HallOfRecords\Scrap\DefaultImageFetcher;
use Stg\HallOfRecords\Scrap\ImageNotFoundException;

class DefaultImageFetcherTest extends \Tests\TestCase
{
    public function testHandles(): void
    {
        $fetcher = $this->createImagerFetcher(
            $this->createMock(HttpClientInterface::class)
        );

        // Fetcher should handle any url.
        self::assertTrue($fetcher->handles(base64_encode(random_bytes(32))));
    }

    public function testFetch(): void
    {
        $url = 'https://example.org/' . md5(random_bytes(32));
        $response = new Response(200, [], random_bytes(64));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')
            ->will(self::returnCallback(function (
                RequestInterface $request
            ) use (
                $url,
                $response
            ): ResponseInterface {
                self::assertSame('GET', $request->getMethod());
                self::assertSame($url, (string)$request->getUri());
                return $response;
            }));

        $fetcher = $this->createImagerFetcher($httpClient);

        self::assertSame([$response], $fetcher->fetch($url));
    }

    public function testFetchWith404(): void
    {
        $url = 'https://example.org/' . md5(random_bytes(32));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')
            ->willReturn(new Response(404));

        $fetcher = $this->createImagerFetcher($httpClient);

        try {
            $fetcher->fetch($url);
            self::fail('Call to `fetch` should throw an exception');
        } catch (ImageNotFoundException $exception) {
            self::assertStringContainsString($url, $exception->getMessage());
        }
    }

    private function createImagerFetcher(
        HttpClientInterface $httpClient
    ): DefaultImageFetcher {
        return new DefaultImageFetcher(
            new HttpContentFetcher($httpClient, $this->userAgent())
        );
    }
}
