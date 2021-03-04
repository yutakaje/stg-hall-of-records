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
use Stg\HallOfRecords\Http\HttpContentFetcher;
use Stg\HallOfRecords\Scrap\DefaultImageFetcher;
use Stg\HallOfRecords\Scrap\ImageFetcherException;

class DefaultImageFetcherTest extends \Tests\TestCase
{
    public function testHandles(): void
    {
        $fetcher = $this->createImagerFetcher([]);

        self::assertTrue($fetcher->handles($this->randomUrl()));
    }

    public function testFetch(): void
    {
        $url = $this->randomUrl();
        $response = new Response(200, [], random_bytes(64));

        $fetcher = $this->createImagerFetcher([
            $url => $response,
        ]);

        self::assertSame([$response], $fetcher->fetch($url));
    }

    public function testFetchWith404(): void
    {
        $url = $this->randomUrl();

        $fetcher = $this->createImagerFetcher([
            $url => new Response(404),
        ]);

        try {
            $fetcher->fetch($url);
            self::fail('Call to `fetch` should throw an exception');
        } catch (ImageFetcherException $exception) {
            self::assertStringContainsString($url, $exception->getMessage());
        }
    }

    /**
     * @param array<string,Response> $responses
     */
    private function createImagerFetcher(array $responses): DefaultImageFetcher
    {
        return new DefaultImageFetcher(
            new HttpContentFetcher(
                $this->createHttpClient(array_map(
                    fn (Response $response) => fn () => $response,
                    $responses
                )),
                $this->userAgent()
            )
        );
    }

    private function randomUrl(): string
    {
        return 'https://www.example.org/' . md5(random_bytes(32));
    }
}
