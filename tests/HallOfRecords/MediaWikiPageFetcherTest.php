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
use Stg\HallOfRecords\MediaWikiPageFetcher;

class MediaWikiPageFetcherTest extends \Tests\TestCase
{
    public function testFetchPage(): void
    {
        $url = 'https://example.com';
        $expected = $this->loadFile(__DIR__ . '/media-wiki-page-fetcher.contents');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')
            ->will(self::returnCallback(function (
                RequestInterface $request
            ) use (
                $url,
                $expected
            ): ResponseInterface {
                self::assertSame(
                    "{$url}/index.php?title=SomePage&action=edit",
                    (string)$request->getUri()
                );
                return new Response(200, [], $this->wrapIntoWikiPage($expected));
            }));

        $fetcher = new MediaWikiPageFetcher($httpClient, $url);

        self::assertSame($expected, $fetcher->fetch('SomePage'));
    }

    public function testFetchPageWithAlias(): void
    {
        $url = 'https://example.com';
        $expected = $this->loadFile(__DIR__ . '/media-wiki-page-fetcher.contents');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')
            ->will(self::returnCallback(function (
                RequestInterface $request
            ) use (
                $url,
                $expected
            ): ResponseInterface {
                self::assertSame(
                    "{$url}/index.php?title=SomePage&action=edit",
                    (string)$request->getUri()
                );
                return new Response(200, [], $this->wrapIntoWikiPage($expected));
            }));

        $fetcher = new MediaWikiPageFetcher($httpClient, $url, [
            'alias-1' => 'OtherPage',
            'database' => 'SomePage',
        ]);

        self::assertSame($expected, $fetcher->fetch('database'));
    }

    public function testFetchRedirectPage(): void
    {
        $url = 'https://example.com';
        $expected = $this->loadFile(__DIR__ . '/media-wiki-page-fetcher.contents');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')
            ->will(self::returnCallback(function (
                RequestInterface $request
            ) use (
                $url,
                $expected
            ): ResponseInterface {
                $requestUrl = (string)$request->getUri();
                switch ($requestUrl) {
                    case "{$url}/index.php?title=AliasPage&action=edit":
                        return new Response(200, [], $this->wrapIntoWikiPage(
                            '#REDIRECT [[RealPage]]'
                        ));

                    case "{$url}/index.php?title=RealPage&action=edit":
                        return new Response(200, [], $this->wrapIntoWikiPage(
                            $expected
                        ));

                    default:
                        self::fail("Unexpected url: `{$requestUrl}`");
                }
            }));

        $fetcher = new MediaWikiPageFetcher($httpClient, $url);

        self::assertSame($expected, $fetcher->fetch('AliasPage'));
    }

    public function testDownloadPage(): void
    {
        $url = 'https://example.com';
        $expected = $this->loadFile(__DIR__ . '/media-wiki-page-fetcher.contents');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')
            ->will(self::returnCallback(function (
                RequestInterface $request
            ) use (
                $url,
                $expected
            ): ResponseInterface {
                self::assertSame(
                    "{$url}/index.php?title=SomePage&action=edit",
                    (string)$request->getUri()
                );
                return new Response(200, [], $this->wrapIntoWikiPage($expected));
            }));

        $fetcher = new MediaWikiPageFetcher($httpClient, $url);

        $response = $fetcher->download('SomePage');

        self::assertSame(
            'text/plain; charset=UTF-8',
            $response->getHeaderLine('Content-Type')
        );
        self::assertMatchesRegularExpression(
            $this->makeAttachmentExpression('SomePage.txt'),
            $response->getHeaderLine('Content-Disposition')
        );
        self::assertSame($expected, (string)$response->getBody());
    }

    public function testDownloadPageWithAlias(): void
    {
        $url = 'https://example.com';
        $expected = $this->loadFile(__DIR__ . '/media-wiki-page-fetcher.contents');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')
            ->will(self::returnCallback(function (
                RequestInterface $request
            ) use (
                $url,
                $expected
            ): ResponseInterface {
                self::assertSame(
                    "{$url}/index.php?title=SomePage&action=edit",
                    (string)$request->getUri()
                );
                return new Response(200, [], $this->wrapIntoWikiPage($expected));
            }));

        $fetcher = new MediaWikiPageFetcher($httpClient, $url, [
            'alias-1' => 'OtherPage',
            'database' => 'SomePage',
        ]);

        $response = $fetcher->download('database');

        self::assertSame(
            'text/plain; charset=UTF-8',
            $response->getHeaderLine('Content-Type')
        );
        self::assertMatchesRegularExpression(
            $this->makeAttachmentExpression('SomePage.txt'),
            $response->getHeaderLine('Content-Disposition')
        );
        self::assertSame($expected, (string)$response->getBody());
    }

    public function testDownloadRedirectPage(): void
    {
        $url = 'https://example.com';
        $expected = $this->loadFile(__DIR__ . '/media-wiki-page-fetcher.contents');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')
            ->will(self::returnCallback(function (
                RequestInterface $request
            ) use (
                $url,
                $expected
            ): ResponseInterface {
                $requestUrl = (string)$request->getUri();
                switch ($requestUrl) {
                    case "{$url}/index.php?title=AliasPage&action=edit":
                        return new Response(200, [], $this->wrapIntoWikiPage(
                            '#REDIRECT [[RealPage]]'
                        ));

                    case "{$url}/index.php?title=RealPage&action=edit":
                        return new Response(200, [], $this->wrapIntoWikiPage(
                            $expected
                        ));

                    default:
                        self::fail("Unexpected url: `{$requestUrl}`");
                }
            }));

        $fetcher = new MediaWikiPageFetcher($httpClient, $url);

        $response = $fetcher->download('AliasPage');

        self::assertSame(
            'text/plain; charset=UTF-8',
            $response->getHeaderLine('Content-Type')
        );
        self::assertMatchesRegularExpression(
            $this->makeAttachmentExpression('AliasPage.txt'),
            $response->getHeaderLine('Content-Disposition')
        );
        self::assertSame($expected, (string)$response->getBody());
    }

    public function testDownloadPageWithFiles(): void
    {
        $url = 'https://example.com';
        $page = $this->loadFile(__DIR__ . '/media-wiki-page-fetcher.contents');
        $files = [
            'Micomlogo.jpg' => random_bytes(32),
            'MICB.jpg' => random_bytes(16),
            'Image with spaces.png' => random_bytes(8),
        ];

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')
            ->will(self::returnCallback(function (
                RequestInterface $request
            ) use (
                $url,
                $page,
                $files
            ): ResponseInterface {
                $requestUrl = (string)$request->getUri();
                switch ($requestUrl) {
                    case "{$url}/index.php?title=SomePage&action=edit":
                        return new Response(200, [], $this->wrapIntoWikiPage($page));

                    case "{$url}/library/File:Micomlogo.jpg":
                        return new Response(200, [], $this->wrapIntoImagePage('/images/Micomlogo.jpg'));
                    case "{$url}/library/File:MICB.jpg":
                        return new Response(200, [], $this->wrapIntoImagePage('/images/MICB.jpg'));
                    case "{$url}/library/File:Image_with_spaces.png":
                        return new Response(200, [], $this->wrapIntoImagePage('/misc/background.png'));

                    case "{$url}/images/Micomlogo.jpg":
                        return new Response(200, [], $files['Micomlogo.jpg']);
                    case "{$url}/images/MICB.jpg":
                        return new Response(200, [], $files['MICB.jpg']);
                    case "{$url}/misc/background.png":
                        return new Response(200, [], $files['Image with spaces.png']);

                    default:
                        self::fail("Unexpected url: `{$requestUrl}`");
                }
            }));

        $fetcher = new MediaWikiPageFetcher($httpClient, $url);

        $response = $fetcher->download('SomePage', true);

        self::assertSame(
            'application/zip',
            $response->getHeaderLine('Content-Type')
        );
        self::assertMatchesRegularExpression(
            $this->makeAttachmentExpression('SomePage.zip'),
            $response->getHeaderLine('Content-Disposition')
        );

        $extractDir = $this->extractZipFile((string)$response->getBody());

        self::assertSame(
            [
                '.',
                '..',
                'files',
                'page.txt'
            ],
            scandir($extractDir)
        );
        self::assertSame(
            [
                '.',
                '..',
                'Image with spaces.png',
                'MICB.jpg',
                'Micomlogo.jpg',
            ],
            scandir("{$extractDir}/files")
        );
    }

    private function wrapIntoWikiPage(string $contents): string
    {
        return str_replace(
            '{{contents}}',
            htmlentities($contents),
            $this->loadFile(__DIR__ . '/media-wiki-page-fetcher.wiki.page')
        );
    }

    private function wrapIntoImagePage(string $filename): string
    {
        return str_replace(
            '{{filename}}',
            htmlentities($filename),
            $this->loadFile(__DIR__ . '/media-wiki-page-fetcher.image.page')
        );
    }

    private function makeAttachmentExpression(string $filename): string
    {
        return '/^attachment; filename="\d{4}-\d{2}-\d{2}T\d{2}-\d{2}_'
            . preg_quote($filename)
            . '"$/';
    }

    private function extractZipFile(string $zipContents): string
    {
        $extractDir = sys_get_temp_dir() . '/stg-download_' . random_int(1, 9999999);
        mkdir($extractDir, 0700);

        $name = tempnam(sys_get_temp_dir(), 'zip');

        if ($name === false) {
            self::fail('Unable to create temporary zip file');
        }

        file_put_contents($name, $zipContents);

        $zip = new \ZipArchive();
        $result = $zip->open($name, \ZipArchive::RDONLY);

        if ($result !== true) {
            self::fail("Unable to create zip file, error {$result}");
        }

        $zip->extractTo($extractDir);
        $zip->close();

        return $extractDir;
    }
}
