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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Stg\HallOfRecords\Http\HttpContentFetcher;
use Stg\HallOfRecords\Import\MediaWiki\ParsedProperties;
use Stg\HallOfRecords\MediaWikiImageScraper;
use Stg\HallOfRecords\MediaWikiPageFetcher;
use Stg\HallOfRecords\Scrap\Extract\UrlExtractorInterface;
use Stg\HallOfRecords\Scrap\ImageFetcherException;
use Stg\HallOfRecords\Scrap\ImageFetcherInterface;
use Stg\HallOfRecords\Scrap\Message;

class MediaWikiImageScraperTest extends \Tests\TestCase
{
    public function testScrap(): void
    {
        $savePath = sys_get_temp_dir() . '/stg-scrap_' . random_int(1, 9999999);
        mkdir($savePath);
        mkdir("{$savePath}/armed_police_batrider");
        mkdir("{$savePath}/armed_police_batrider/13456940_c2f65f5e86cbadded7fdfcc8ddc3d76f");

        $imageResponses = [
            0 => $this->createImageResponses(
                'http://example.com/photozou/1171_624.v1610.jpg',
                [$this->createImageResponse('image/jpeg', $this->randomPayload())]
            ),
            1 => $this->createImageResponses(
                'https://example.org/twitter/9823498.jpg',
                [$this->createErrorResponse(404)]
            ),
            2 => $this->createImageResponses(
                'https://example.org/twitpic/53895f',
                [$this->createImageResponse('image/png', $this->randomPayload())]
            ),
            3 => $this->createImageResponses(
                'http://example.org/jp/20588_624.v16882.jpg',
                [
                    $this->createImageResponse('image/jpeg', $this->randomPayload()),
                    $this->createImageResponse('image/png', $this->randomPayload()),
                ]
            ),
            4 => $this->createImageResponses(
                'http://example.org/grema-images/01.png',
                [$this->createImageResponse('image/png', $this->randomPayload())]
            ),
            5 => $this->createImageResponses(
                'http://example.org/grema-images/02.png',
                [$this->createErrorResponse(403)]
            ),
        ];

        $scraper = new MediaWikiImageScraper(
            $this->createPageFetcher(
                $this->loadFile(__DIR__ . '/media-wiki-image-scraper.database')
            ),
            $this->createUrlExtractor(),
            $this->createImageFetcher($imageResponses)
        );

        $scraper->scrap($savePath);

        $this->assertBackedUpFiles($savePath, $this->createExpectedImage(
            'armed_police_batrider/29449270_39f2dd14ddff797fa4bfd3effac87e43',
            $imageResponses[0]->url,
            [
                $this->createExpectedFile('image.jpg', $imageResponses[0]->httpResponses[0]),
            ]
        ));
        $this->assertBackedUpFiles($savePath, $this->createExpectedImage(
            'armed_police_batrider/23053160_8c2654ade5fea2fcf098a9ddd07370e9',
            $imageResponses[2]->url,
            [
                $this->createExpectedFile('image.png', $imageResponses[2]->httpResponses[0]),
            ]
        ));
        $this->assertBackedUpFiles($savePath, $this->createExpectedImage(
            'armed_police_batrider/14183520_8b4ad58db47103ddcabe37946228abe4',
            $imageResponses[3]->url,
            [
                $this->createExpectedFile('image-1.jpg', $imageResponses[3]->httpResponses[0]),
                $this->createExpectedFile('image-2.png', $imageResponses[3]->httpResponses[1]),
            ]
        ));
        $this->assertBackedUpFiles($savePath, $this->createExpectedImage(
            'great_mahou_daisakusen/87818460_f72db8782ae3a8ab20ed381c109fa8bb',
            $imageResponses[4]->url,
            [
                $this->createExpectedFile('image.png', $imageResponses[4]->httpResponses[0]),
            ]
        ));

        self::assertEquals(array_merge(
            [
                $this->createMessage('info', 'Scrapping started'),
            ],
            $this->addGameContext('armed_police_batrider', [
                $this->createMessage('info', 'Scrapping from game'),
                $this->addScoreContext('armed_police_batrider/29449270', [
                    $this->createMessage('info', 'Scrapping from score'),
                    $this->addImageContext('armed_police_batrider/29449270_39f2dd14ddff797fa4bfd3effac87e43', [
                        $this->addUrlContext('http://example.com/photozou/1171_624.v1610.jpg', [
                            $this->createMessage('info', 'Fetching image'),
                            $this->createMessage('info', 'Image fetched'),
                        ]),
                        $this->createMessage('success', 'Image saved'),
                    ]),
                    $this->createMessage('info', 'Scrapped from score'),
                ]),
                $this->addScoreContext('armed_police_batrider/29737030', [
                    $this->createMessage('info', 'Scrapping from score'),
                    $this->addImageContext('armed_police_batrider/29737030_9b8733fcb67b87b66a26f38f998791a6', [
                        $this->addUrlContext('https://example.org/twitter/9823498.jpg', [
                            $this->createMessage('info', 'Fetching image'),
                            $this->createMessage('error', 'Image not found', [
                                'error' => 'some error messsage',
                            ]),
                        ]),
                    ]),
                    $this->createMessage('info', 'Scrapped from score'),
                ]),
                $this->addScoreContext('armed_police_batrider/23053160', [
                    $this->createMessage('info', 'Scrapping from score'),
                    $this->addImageContext('armed_police_batrider/23053160_8c2654ade5fea2fcf098a9ddd07370e9', [
                        $this->addUrlContext('https://example.org/twitpic/53895f', [
                            $this->createMessage('info', 'Fetching image'),
                            $this->createMessage('info', 'Image fetched'),
                        ]),
                        $this->createMessage('success', 'Image saved'),
                    ]),
                    $this->createMessage('info', 'Scrapped from score'),
                ]),
                $this->addScoreContext('armed_police_batrider/14183520', [
                    $this->createMessage('info', 'Scrapping from score'),
                    $this->addImageContext('armed_police_batrider/14183520_8b4ad58db47103ddcabe37946228abe4', [
                        $this->addUrlContext('http://example.org/jp/20588_624.v16882.jpg', [
                            $this->createMessage('info', 'Fetching image'),
                            $this->createMessage('info', 'Image fetched'),
                        ]),
                        $this->createMessage('success', 'Image saved'),
                    ]),
                    $this->createMessage('info', 'Scrapped from score'),
                ]),
                $this->addScoreContext('armed_police_batrider/13456940', [
                    $this->createMessage('info', 'Scrapping from score'),
                    $this->addImageContext('armed_police_batrider/13456940_c2f65f5e86cbadded7fdfcc8ddc3d76f', [
                        $this->addUrlContext('https://example.org/twitpic/5q2ocm', [
                            $this->createMessage('info', 'Image already exists'),
                        ]),
                    ]),
                    $this->createMessage('info', 'Scrapped from score'),
                ]),
                $this->createMessage('info', 'Scrapped from game'),
            ]),
            $this->addGameContext('great_mahou_daisakusen', [
                $this->createMessage('info', 'Scrapping from game'),
                $this->addScoreContext('great_mahou_daisakusen/87818460', [
                    $this->createMessage('info', 'Scrapping from score'),
                    $this->addImageContext('great_mahou_daisakusen/87818460_f72db8782ae3a8ab20ed381c109fa8bb', [
                        $this->addUrlContext('http://example.org/grema-images/01.png', [
                            $this->createMessage('info', 'Fetching image'),
                            $this->createMessage('info', 'Image fetched'),
                        ]),
                        $this->createMessage('success', 'Image saved'),
                    ]),
                    $this->createMessage('info', 'Scrapped from score'),
                ]),
                $this->addScoreContext('great_mahou_daisakusen/94447870', [
                    $this->createMessage('info', 'Scrapping from score'),
                    $this->addImageContext('great_mahou_daisakusen/94447870_a2837055a38c98171a2c5ed91077b4c6', [
                        $this->addUrlContext('http://example.org/grema-images/02.png', [
                            $this->createMessage('info', 'Fetching image'),
                            $this->createMessage('error', 'Image not found', [
                                'error' => 'some error messsage',
                            ]),
                        ]),
                    ]),
                    $this->createMessage('info', 'Scrapped from score'),
                ]),
                $this->createMessage('info', 'Scrapped from game'),
            ]),
            [
                $this->createMessage('info', 'Scrapping complete'),
            ]
        ), $scraper->getMessages());
    }

    public function testScrapWithRuntimeLimit(): void
    {
        $runtimeLimit = random_int(10, 50) / 100;
        $savePath = sys_get_temp_dir() . '/stg-scrap_' . random_int(1, 9999999);

        $imageResponse = $this->createImageResponses(
            'https://example.org/uri',
            [$this->createErrorResponse(404)]
        );

        $wrap = fn (string $value) => <<<YAML
<nowiki>
{$value}
</nowiki>
YAML;
        $createGameEntry = fn (int $i) => $wrap(<<<YAML
name: some-game-{$i}
scores:
  - image-urls:
      - https://example.org/uri
YAML);

        $database = $wrap('name: global') . implode(PHP_EOL, array_map(
            fn (int $i) => $createGameEntry($i),
            range(1, 1000)
        ));

        $payload = str_replace(
            '{{contents}}',
            htmlentities($database),
            $this->loadFile(__DIR__ . '/media-wiki-page-fetcher.wiki.page')
        );

        $scraper = new MediaWikiImageScraper(
            $this->createPageFetcher($payload),
            $this->createUrlExtractor(),
            $this->createImageFetcher([$imageResponse])
        );

        $startTime = microtime(true);
        $scraper->scrap($savePath, $runtimeLimit);
        $elapsedTime = microtime(true) - $startTime;

        self::assertEqualsWithDelta($runtimeLimit, $scraper->getElapsedTime(), 0.01);
        self::assertLessThan($elapsedTime, $scraper->getElapsedTime());
        self::assertEquals(
            [
                $this->createMessage('info', 'Scrapping started'),
                $this->createMessage('info', 'Runtime limit reached'),
            ],
            array_merge(
                array_slice($scraper->getMessages(), 0, 1),
                array_slice($scraper->getMessages(), -1, 1)
            )
        );
    }

    private function assertBackedUpFiles(
        string $savePath,
        \stdClass $expectedImage
    ): void {
        $directory = "{$savePath}/{$expectedImage->id}";

        foreach ($expectedImage->files as $file) {
            self::assertSame(
                $file->payload,
                $this->loadFile("{$directory}/{$file->filename}")
            );
        }

        self::assertSame(
            [
                'id' => $expectedImage->id,
                'url' => $expectedImage->url,
                'files' => array_map(
                    fn (\stdClass $file) => [
                        'filename' => $file->filename,
                        'mimeType' => $file->mimeType,
                    ],
                    $expectedImage->files
                )
            ],
            json_decode($this->loadFile("{$directory}/meta.json"), true)
        );
    }

    private function randomPayload(): string
    {
        return random_bytes(random_int(16, 64));
    }

    private function createPageFetcher(string $payload): MediaWikiPageFetcher
    {
        $url = 'https://www.example.org';

        return new MediaWikiPageFetcher($this->createHttpClient([
            "{$url}/index.php?title=database&action=edit" => fn () => new Response(
                200,
                [],
                $payload
            ),
        ]), $url);
    }

    private function createUrlExtractor(): UrlExtractorInterface
    {
        $extractor = $this->createMock(UrlExtractorInterface::class);
        $extractor->method('extractUrls')->will(self::returnCallback(
            function (ParsedProperties $score): array {
                return $score->get('image-urls', []);
            }
        ));

        return $extractor;
    }

    /**
     * @param \stdClass[] $responses
     */
    private function createImageFetcher(array $responses): ImageFetcherInterface
    {
        $fetcher = $this->createMock(ImageFetcherInterface::class);
        $fetcher->method('handles')->willReturn(true);
        $fetcher->method('fetch')
            ->will(self::returnCallback(function (string $url) use (
                $responses
            ): array {
                foreach ($responses as $response) {
                    if ($response->url !== $url) {
                        continue;
                    }

                    foreach ($response->httpResponses as $httpResponse) {
                        if ($httpResponse->getStatusCode() !== 200) {
                            throw new ImageFetcherException('some error messsage');
                        }
                    }

                    return $response->httpResponses;
                }
                self::fail("Response for `{$url}` does not exist");
            }));

        return $fetcher;
    }

    /**
     * @param \stdClass[] $files
     */
    private function createExpectedImage(
        string $id,
        string $url,
        array $files
    ): \stdClass {
        $image = new \stdClass();
        $image->id = $id;
        $image->url = $url;
        $image->files = $files;
        return $image;
    }

    private function createExpectedFile(
        string $filename,
        ResponseInterface $response
    ): \stdClass {
        $file = new \stdClass();
        $file->filename = $filename;
        $file->mimeType = $response->getHeaderLine('Content-Type');
        $file->payload = (string)$response->getBody();
        return $file;
    }

    /**
     * @param ResponseInterface[] $httpResponses
     */
    private function createImageResponses(
        string $url,
        array $httpResponses
    ): \stdClass {
        $responses = new \stdClass();
        $responses->url = $url;
        $responses->httpResponses = $httpResponses;
        return $responses;
    }

    private function createImageResponse(
        string $contentType,
        string $payload
    ): ResponseInterface {
        return new Response(
            200,
            ['Content-Type' => $contentType],
            $payload
        );
    }

    private function createErrorResponse(int $httpStatus): ResponseInterface
    {
        return new Response($httpStatus);
    }

    /**
     * @param array<string,mixed> $context
     */
    private function createMessage(
        string $type,
        string $message,
        array $context = []
    ): Message {
        return new Message($message, ['type' => $type] + $context);
    }

    /**
     * @param mixed[] $messages
     * @return Message[]
     */
    private function addGameContext(string $game, array $messages): array
    {
        return $this->addContext(['game' => $game], $messages);
    }

    /**
     * @param mixed[] $messages
     * @return Message[]
     */
    private function addScoreContext(string $score, array $messages): array
    {
        return $this->addContext(['score' => $score], $messages);
    }

    /**
     * @param mixed[] $messages
     * @return Message[]
     */
    private function addUrlContext(string $url, array $messages): array
    {
        return $this->addContext(['url' => $url], $messages);
    }

    /**
     * @param mixed[] $messages
     * @return Message[]
     */
    private function addImageContext(string $image, array $messages): array
    {
        return $this->addContext(['image' => $image], $messages);
    }

    /**
     * @param array<string,mixed> $context
     * @param mixed[] $messages
     * @return Message[]
     */
    private function addContext(array $context, array $messages): array
    {
        return array_reduce(
            $messages,
            function (array $all, $message) use ($context): array {
                if ($message instanceof Message) {
                    $all[] = new Message(
                        $message->message(),
                        $context + $message->context(),
                    );
                } elseif (is_array($message)) {
                    return array_merge($all, $this->addContext($context, $message));
                } else {
                    self::fail('Not sure what to do with this message');
                }
                return $all;
            },
            []
        );
    }
}
