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
use Stg\HallOfRecords\Scrap\Extract\TwitterUrlExtractor;

class TwitterUrlExtractorTest extends \Tests\TestCase
{
    public function testExtractUrls(): void
    {
        $imageUrls = [
            $this->randomUrl(),
            $this->randomUrl(),
        ];

        $extractor = new TwitterUrlExtractor();

        self::assertEmpty($extractor->extractUrls(
            new ParsedProperties()
        ));
        self::assertEmpty($extractor->extractUrls(
            new ParsedProperties([
                'image-urls' => [
                    $this->randomUrl(),
                    $this->randomUrl(),
                ],
            ])
        ));
        self::assertSame($imageUrls, $extractor->extractUrls(
            new ParsedProperties([
                'sources' => [
                    new ParsedProperties([
                        'name' => 'Youtube',
                        'url' => $this->randomUrl(),
                    ]),
                    new ParsedProperties([
                        'name' => 'Twitter',
                        'url' => $imageUrls[0],
                    ]),
                    new ParsedProperties([
                        'name' => 'Twitter',
                        'url' => $imageUrls[1],
                    ]),
                ],
                'image-urls' => [
                    $this->randomUrl(),
                    $this->randomUrl(),
                ],
            ])
        ));
    }

    private function randomUrl(): string
    {
        return 'https://www.example.org/' . md5(random_bytes(32));
    }
}
