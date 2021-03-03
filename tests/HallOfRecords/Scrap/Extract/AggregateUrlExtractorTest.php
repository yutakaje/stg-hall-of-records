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

namespace Stg\HallOfRecords\Tests\HallOfRecords\Scrap\Extract;

use Stg\HallOfRecords\Import\MediaWiki\ParsedProperties;
use Stg\HallOfRecords\Scrap\Extract\AggregateUrlExtractor;
use Stg\HallOfRecords\Scrap\Extract\UrlExtractorInterface;

class AggregateUrlExtractorTest extends \Tests\TestCase
{
    public function testExtractUrls(): void
    {
        $imageUrls = [
            $this->randomUrl(),
            $this->randomUrl(),
            $this->randomUrl(),
            $this->randomUrl(),
            $this->randomUrl(),
        ];

        $extractors = [
            $this->createMock(UrlExtractorInterface::class),
            $this->createMock(UrlExtractorInterface::class),
            $this->createMock(UrlExtractorInterface::class),
        ];

        $extractors[0]->method('extractUrls')->willReturn([
            $imageUrls[0],
            $imageUrls[1],
        ]);
        $extractors[1]->method('extractUrls')->willReturn([
            $imageUrls[2],
            $imageUrls[3],
        ]);
        $extractors[2]->method('extractUrls')->willReturn([
            $imageUrls[4],
        ]);

        $this->assertExtractUrls([], new AggregateUrlExtractor([]));
        $this->assertExtractUrls(
            [
                $imageUrls[0],
                $imageUrls[1],
                $imageUrls[4],
            ],
            new AggregateUrlExtractor([
                $extractors[0],
                $extractors[2],
            ])
        );
        $this->assertExtractUrls($imageUrls, new AggregateUrlExtractor([
            $extractors[0],
            $extractors[1],
            $extractors[2],
        ]));
    }

    /**
     * @param string[] $expectedUrls
     */
    private function assertExtractUrls(
        array $expectedUrls,
        AggregateUrlExtractor $extractor
    ): void {
        self::assertSame($expectedUrls, $extractor->extractUrls(new ParsedProperties()));
    }

    private function randomUrl(): string
    {
        return 'https://www.example.org/' . md5(random_bytes(32));
    }
}
