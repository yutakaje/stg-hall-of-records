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

namespace Tests\HallOfRecords;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Stg\HallOfRecords\MediaWikiImageScraper;
use Stg\HallOfRecords\MediaWikiPageFetcher;
use Stg\HallOfRecords\Scrap\ImageFetcherInterface;
use Stg\HallOfRecords\Scrap\Message;

class MediaWikiImageScraperTest extends \Tests\TestCase
{
    public function testScrap(): void
    {
        $backupPath = sys_get_temp_dir() . '/stg-scrap_' . random_int(1, 9999999);
        mkdir($backupPath);
        mkdir("{$backupPath}/armed_police_batrider");
        mkdir("{$backupPath}/armed_police_batrider/13456940_c2f65f5e86cbadded7fdfcc8ddc3d76f");

        $imageResponses = [
            0 => $this->createImageResponse(
                'http://example.com/photozou/1171_624.v1610.jpg',
                'image/jpeg',
                $this->randomPayload()
            ),
            1 => $this->createImageResponse(
                'https://example.org/twitpic/53895f',
                'image/png',
                $this->randomPayload()
            ),
            2 => $this->createImageResponse(
                'http://example.org/jp/20588_624.v16882.jpg',
                'image/jpeg',
                $this->randomPayload()
            ),
            3 => $this->createImageResponse(
                'http://example.org/grema-images/01.png',
                'image/png',
                $this->randomPayload()
            ),
        ];

        $scraper = new MediaWikiImageScraper(
            $this->createPageFetcher(),
            $this->createImageFetchers($imageResponses),
            $backupPath
        );

        $scraper->scrap();

        $this->assertBackedUpFiles(
            "{$backupPath}/armed_police_batrider/29449270_39f2dd14ddff797fa4bfd3effac87e43",
            '.jpg',
            $imageResponses[0]
        );
        $this->assertBackedUpFiles(
            "{$backupPath}/armed_police_batrider/23053160_8c2654ade5fea2fcf098a9ddd07370e9",
            '.png',
            $imageResponses[1]
        );
        $this->assertBackedUpFiles(
            "{$backupPath}/armed_police_batrider/14183520_8b4ad58db47103ddcabe37946228abe4",
            '.jpg',
            $imageResponses[2]
        );
        $this->assertBackedUpFiles(
            "{$backupPath}/great_mahou_daisakusen/87818460_f72db8782ae3a8ab20ed381c109fa8bb",
            '.png',
            $imageResponses[3]
        );

        self::assertEquals([
            $this->createMessage('Scrapping from game', 'armed_police_batrider'),
            $this->createMessage('Scrapping from score', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/29449270',
            ]),
            $this->createMessage('Fetching image', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/29449270',
                'url' => 'http://example.com/photozou/1171_624.v1610.jpg',
            ]),
            $this->createMessage('Image fetched', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/29449270',
                'url' => 'http://example.com/photozou/1171_624.v1610.jpg',
            ]),
            $this->createMessage('Image saved', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/29449270',
                'image' => 'armed_police_batrider/29449270_39f2dd14ddff797fa4bfd3effac87e43',
            ]),
            $this->createMessage('Scrapped from score', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/29449270',
            ]),
            $this->createMessage('Scrapping from score', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/23053160',
            ]),
            $this->createMessage('Fetching image', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/23053160',
                'url' => 'https://example.org/twitpic/53895f',
            ]),
            $this->createMessage('Image fetched', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/23053160',
                'url' => 'https://example.org/twitpic/53895f'
            ]),
            $this->createMessage('Image saved', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/23053160',
                'image' => 'armed_police_batrider/23053160_8c2654ade5fea2fcf098a9ddd07370e9',
            ]),
            $this->createMessage('Scrapped from score', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/23053160',
            ]),
            $this->createMessage('Scrapping from score', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/14183520',
            ]),
            $this->createMessage('Fetching image', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/14183520',
                'url' => 'http://example.org/jp/20588_624.v16882.jpg',
            ]),
            $this->createMessage('Image fetched', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/14183520',
                'url' => 'http://example.org/jp/20588_624.v16882.jpg',
            ]),
            $this->createMessage('Image saved', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/14183520',
                'image' => 'armed_police_batrider/14183520_8b4ad58db47103ddcabe37946228abe4',
            ]),
            $this->createMessage('Scrapped from score', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/14183520',
            ]),
            $this->createMessage('Scrapping from score', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/13456940',
            ]),
            $this->createMessage('Image already exists', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/13456940',
                'image' => 'armed_police_batrider/13456940_c2f65f5e86cbadded7fdfcc8ddc3d76f',
                'url' => 'https://example.org/twitpic/5q2ocm',
            ]),
            $this->createMessage('Scrapped from score', 'armed_police_batrider', [
                'score' => 'armed_police_batrider/13456940',
            ]),
            $this->createMessage('Scrapped from game', 'armed_police_batrider'),
            $this->createMessage('Scrapping from game', 'great_mahou_daisakusen'),
            $this->createMessage('Scrapping from score', 'great_mahou_daisakusen', [
                'score' => 'great_mahou_daisakusen/87818460',
            ]),
            $this->createMessage('Fetching image', 'great_mahou_daisakusen', [
                'score' => 'great_mahou_daisakusen/87818460',
                'url' => 'http://example.org/grema-images/01.png',
            ]),
            $this->createMessage('Image fetched', 'great_mahou_daisakusen', [
                'score' => 'great_mahou_daisakusen/87818460',
                'url' => 'http://example.org/grema-images/01.png',
            ]),
            $this->createMessage('Image saved', 'great_mahou_daisakusen', [
                'score' => 'great_mahou_daisakusen/87818460',
                'image' => 'great_mahou_daisakusen/87818460_f72db8782ae3a8ab20ed381c109fa8bb',
            ]),
            $this->createMessage('Scrapped from score', 'great_mahou_daisakusen', [
                'score' => 'great_mahou_daisakusen/87818460',
            ]),
            $this->createMessage('Scrapped from game', 'great_mahou_daisakusen'),
        ], $scraper->getMessages());
    }

    /**
     * @param array<string,mixed> $expected
     */
    private function assertBackedUpFiles(
        string $directory,
        string $imageExtension,
        array $expected
    ): void {
        self::assertSame(
            $this->loadFile("{$directory}/image{$imageExtension}"),
            (string)$expected['response']->getBody()
        );
        self::assertSame(
            json_decode($this->loadFile("{$directory}/meta.json"), true),
            [
                'url' => $expected['url'],
                'mimeType' => $expected['response']->getHeaderLine('Content-Type'),
            ]
        );
    }

    private function randomPayload(): string
    {
        return random_bytes(random_int(16, 64));
    }

    private function createPageFetcher(): MediaWikiPageFetcher
    {
        $url = 'https://www.example.org';

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')
            ->will(self::returnCallback(function (
                RequestInterface $request
            ) use (
                $url
            ): ResponseInterface {
                self::assertSame(
                    "{$url}/index.php?title=database&action=edit",
                    (string)$request->getUri()
                );
                return new Response(200, [], $this->loadFile(
                    __DIR__ . '/media-wiki-image-scraper.database'
                ));
            }));

        return new MediaWikiPageFetcher($httpClient, $url);
    }

    /**
     * @param array<string,mixed>[] $imageResponses
     * @return ImageFetcherInterface[]
     */
    private function createImageFetchers(array $imageResponses): array
    {
        $fetchers = [
            $this->createMock(ImageFetcherInterface::class),
            $this->createMock(ImageFetcherInterface::class),
            $this->createMock(ImageFetcherInterface::class),
        ];

        $fetchers[0]->method('handles')->willReturn(false);
        $fetchers[1]->method('handles')->willReturn(false);
        $fetchers[2]->method('handles')->willReturn(true);
        $fetchers[2]->method('fetch')
            ->will(self::returnCallback(function (string $url) use (
                $imageResponses
            ): ResponseInterface {
                foreach ($imageResponses as $imageResponse) {
                    if ($imageResponse['url'] === $url) {
                        return $imageResponse['response'];
                    }
                }
                self::fail("Response for `{$url}` does not exist");
            }));

        return $fetchers;
    }

    /**
     * @return array<string,mixed>
     */
    private function createImageResponse(
        string $url,
        string $contentType,
        string $payload
    ): array {
        return [
            'url' => $url,
            'response' => new Response(
                200,
                ['Content-Type' => $contentType],
                $payload
            ),
        ];
    }

    /**
     * @param array<string,mixed> $context
     */
    private function createMessage(
        string $message,
        ?string $game = null,
        array $context = []
    ): Message {
        return new Message(
            $message,
            ['game' => $game] + $context,
        );
    }
}
