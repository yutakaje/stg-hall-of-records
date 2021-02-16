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
use Stg\HallOfRecords\Error\StgException;
use Stg\HallOfRecords\Http\HttpContentFetcher;
use Stg\HallOfRecords\Scrap\ImageNotFoundException;
use Stg\HallOfRecords\Scrap\TwitterImageFetcher;

class TwitterImageFetcherTest extends \Tests\TestCase
{
    public function testHandles(): void
    {
        $fetcher = $this->createImagerFetcher(
            $this->createMock(HttpClientInterface::class)
        );

        self::assertTrue($fetcher->handles($this->tweetUrl()));
        self::assertFalse($fetcher->handles('https://twitter.com/' . md5(random_bytes(32))));
        self::assertFalse($fetcher->handles('https://example.org/' . md5(random_bytes(32))));
    }

    public function testFetch(): void
    {
        $username = 'DareKa' . random_int(1, 99999);
        $tweetId = (string)random_int(1000000, 9999999);
        $guestToken = str_shuffle('3199159699012258891');

        $responses = [
            'indexPage' => $this->indexPageResponse(),
            'mainJs' => $this->mainJsResponse(),
            'guestToken' => $this->guestTokenResponse($guestToken),
            'tweetJson' => $this->tweetJsonResponse($tweetId, $username),
            'image1' => new Response(200, [], random_bytes(64)),
            'image2' => new Response(200, [], random_bytes(64)),
            'image3' => new Response(200, [], random_bytes(64)),
        ];

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')
            ->will(self::returnCallback(function (RequestInterface $request) use (
                $tweetId,
                $guestToken,
                $responses
            ): ResponseInterface {
                $requestUrl = (string)$request->getUri()->withQuery('');
                switch ($requestUrl) {
                    case 'https://twitter.com/':
                        self::assertSame('GET', $request->getMethod());
                        return $responses['indexPage'];

                    case 'https://abs.twimg.com/responsive-web/client-web/main.40da0595.js':
                        self::assertSame('GET', $request->getMethod());
                        return $responses['mainJs'];

                    case 'https://api.twitter.com/1.1/guest/activate.json':
                        self::assertSame('POST', $request->getMethod());
                        self::assertSame(
                            $this->userAgent(),
                            $request->getHeaderLine('User-Agent')
                        );
                        self::assertSame(
                            'application/json',
                            $request->getHeaderLine('Content-Type')
                        );
                        self::assertSame(
                            'Bearer AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUjeROCu5E6HI8'
                            . 'nZzx4upTs3D1%vZt7ft8kF81IULq1cH6hjTLJu4vF33AGAWWCpjnTA',
                            $request->getHeaderLine('Authorization')
                        );
                        return $responses['guestToken'];

                    case "https://api.twitter.com/2/timeline/conversation/{$tweetId}.json":
                        self::assertSame('GET', $request->getMethod());
                        self::assertSame(
                            'include_profile_interstitial_type=1'
                            . '&include_blocking=1'
                            . '&include_blocked_by=1'
                            . '&include_followed_by=1'
                            . '&include_want_retweets=1'
                            . '&include_mute_edge=1'
                            . '&include_can_dm=1'
                            . '&include_can_media_tag=1'
                            . '&skip_status=1'
                            . '&cards_platform=Web-12'
                            . '&include_cards=1'
                            . '&include_ext_alt_text=true'
                            . '&include_quote_count=true'
                            . '&include_reply_count=1'
                            . '&tweet_mode=extended'
                            . '&include_entities=true'
                            . '&include_user_entities=true'
                            . '&include_ext_media_color=true'
                            . '&include_ext_media_availability=true'
                            . '&send_error_codes=true'
                            . '&simple_quoted_tweet=true'
                            . '&count=20'
                            . '&include_ext_has_birdwatch_notes=false'
                            . '&ext=mediaStats%2ChighlightedLabel',
                            $request->getUri()->getQuery()
                        );
                        self::assertSame(
                            $this->userAgent(),
                            $request->getHeaderLine('User-Agent')
                        );
                        self::assertSame(
                            'Bearer AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUjeROCu5E6HI8'
                            . 'nZzx4upTs3D1%vZt7ft8kF81IULq1cH6hjTLJu4vF33AGAWWCpjnTA',
                            $request->getHeaderLine('Authorization')
                        );
                        self::assertSame($guestToken, $request->getHeaderLine('X-Guest-Token'));
                        return $responses['tweetJson'];

                    case 'https://pbs.twimg.com/media/3b8i0pTLAA4iQ.jpg':
                        return $responses['image1'];

                    case 'https://pbs.twimg.com/media/XobAEGfUwFFxEq7.jpg':
                        return $responses['image2'];

                    case 'https://pbs.twimg.com/media/Y5dUm984ABAgQ1Ct.jpg':
                        return $responses['image3'];

                    default:
                        self::fail("Unexpected url: `{$requestUrl}`");
                }
            }));

        $fetcher = $this->createImagerFetcher($httpClient);

        self::assertSame(
            [
                $responses['image1'],
                $responses['image2'],
                $responses['image3'],
            ],
            $fetcher->fetch(
                $this->tweetUrl($username, $tweetId)
            )
        );
    }

    public function testFetchWith404OnIndexPage(): void
    {
        $this->testFetchWith404OnTwitterPage('indexPage');
    }

    public function testFetchWith404OnMainJs(): void
    {
        $this->testFetchWith404OnTwitterPage('mainJs');
    }

    public function testFetchWith404OnGuestToken(): void
    {
        $this->testFetchWith404OnTwitterPage('guestToken');
    }

    public function testFetchWith404OnTweetJson(): void
    {
        $this->testFetchWith404OnTwitterPage('tweetJson');
    }

    private function testFetchWith404OnTwitterPage(string $pageName): void
    {
        $username = 'DareKa' . random_int(1, 99999);
        $tweetId = (string)random_int(1000000, 9999999);
        $guestToken = str_shuffle('3199159699012258891');

        $urls = [
            'indexPage' => $this->indexPageUrl(),
            'mainJs' => $this->mainJsUrl(),
            'guestToken' => $this->guestTokenUrl(),
            'tweetJson' => $this->tweetJsonUrl($tweetId),
        ];

        $responses = [
            'indexPage' => $this->indexPageResponse(),
            'mainJs' => $this->mainJsResponse(),
            'guestToken' => $this->guestTokenResponse($guestToken),
            'tweetJson' => $this->tweetJsonResponse($tweetId, $username),
        ];

        $responses[$pageName] = new Response(404);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')
            ->will(self::returnCallback(function (RequestInterface $request) use (
                $urls,
                $responses
            ): ResponseInterface {
                $requestUrl = (string)$request->getUri()->withQuery('');
                foreach ($urls as $name => $url) {
                    if ($requestUrl === $url) {
                        return $responses[$name];
                    }
                }
                self::fail("Unexpected url: `{$requestUrl}`");
            }));

        $fetcher = $this->createImagerFetcher($httpClient);

        try {
            $fetcher->fetch(
                $this->tweetUrl($username, $tweetId)
            );
            self::fail('Call to `fetch` should throw an exception');
        } catch (StgException $exception) {
            self::assertStringContainsString($urls[$pageName], $exception->getMessage());
        }
    }

    public function testFetchWith404OnImage1(): void
    {
        $this->testFetchWith404OnImage('image1');
    }

    public function testFetchWith404OnImage2(): void
    {
        $this->testFetchWith404OnImage('image2');
    }

    public function testFetchWith404OnImage3(): void
    {
        $this->testFetchWith404OnImage('image3');
    }

    private function testFetchWith404OnImage(string $imageName): void
    {
        $username = 'DareKa' . random_int(1, 99999);
        $tweetId = (string)random_int(1000000, 9999999);
        $guestToken = str_shuffle('3199159699012258891');

        $urls = [
            'indexPage' => $this->indexPageUrl(),
            'mainJs' => $this->mainJsUrl(),
            'guestToken' => $this->guestTokenUrl(),
            'tweetJson' => $this->tweetJsonUrl($tweetId),
            'image1' => 'https://pbs.twimg.com/media/3b8i0pTLAA4iQ.jpg',
            'image2' => 'https://pbs.twimg.com/media/XobAEGfUwFFxEq7.jpg',
            'image3' => 'https://pbs.twimg.com/media/Y5dUm984ABAgQ1Ct.jpg',
        ];

        $responses = [
            'indexPage' => $this->indexPageResponse(),
            'mainJs' => $this->mainJsResponse(),
            'guestToken' => $this->guestTokenResponse($guestToken),
            'tweetJson' => $this->tweetJsonResponse($tweetId, $username),
            'image1' => new Response(200, [], random_bytes(64)),
            'image2' => new Response(200, [], random_bytes(64)),
            'image3' => new Response(200, [], random_bytes(64)),
        ];

        $responses[$imageName] = new Response(404);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')
            ->will(self::returnCallback(function (RequestInterface $request) use (
                $urls,
                $responses
            ): ResponseInterface {
                $requestUrl = (string)$request->getUri()->withQuery('');
                foreach ($urls as $name => $url) {
                    if ($requestUrl === $url) {
                        return $responses[$name];
                    }
                }
                self::fail("Unexpected url: `{$requestUrl}`");
            }));

        $fetcher = $this->createImagerFetcher($httpClient);

        try {
            $fetcher->fetch(
                $this->tweetUrl($username, $tweetId)
            );
            self::fail('Call to `fetch` should throw an exception');
        } catch (ImageNotFoundException $exception) {
            self::assertStringContainsString($urls[$imageName], $exception->getMessage());
        }
    }

    private function tweetUrl(
        ?string $username = null,
        ?string $tweetId = null
    ): string {
        if ($username === null) {
            $username = 'DareKa' . random_int(1, 99999);
        }

        if ($tweetId === null) {
            $tweetId = random_int(1000000, 9999999);
        }

        return "https://twitter.com/{$username}/status/{$tweetId}";
    }

    private function indexPageUrl(): string
    {
        return 'https://twitter.com/';
    }

    private function mainJsUrl(): string
    {
        return 'https://abs.twimg.com/responsive-web/client-web/main.40da0595.js';
    }

    private function guestTokenUrl(): string
    {
        return 'https://api.twitter.com/1.1/guest/activate.json';
    }

    private function tweetJsonUrl(string $tweetId): string
    {
        return "https://api.twitter.com/2/timeline/conversation/{$tweetId}.json";
    }

    private function indexPageResponse(): ResponseInterface
    {
        return new Response(200, [], $this->loadTwitterFile('index.html'));
    }

    private function mainJsResponse(): ResponseInterface
    {
        return new Response(200, [], $this->loadTwitterFile('main.js'));
    }

    private function guestTokenResponse(string $guestToken): ResponseInterface
    {
        return new Response(200, [], '{"guest_token":"' . $guestToken . '"}');
    }

    private function tweetJsonResponse(
        string $tweetId,
        string $username
    ): ResponseInterface {
        return new Response(200, [], str_replace(
            ['{{ tweetId }}', '{{ username }}'],
            [$tweetId, $username],
            $this->loadTwitterFile('tweet.json')
        ));
    }

    private function loadTwitterFile(string $name): string
    {
        return $this->loadFile(__DIR__ . "/twitter-{$name}");
    }

    private function createImagerFetcher(
        HttpClientInterface $httpClient
    ): TwitterImageFetcher {
        return new TwitterImageFetcher(
            new HttpContentFetcher($httpClient, $this->userAgent())
        );
    }
}
