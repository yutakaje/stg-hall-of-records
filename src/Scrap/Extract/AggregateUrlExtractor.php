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

namespace Stg\HallOfRecords\Scrap\Extract;

use Stg\HallOfRecords\Import\MediaWiki\ParsedProperties;

final class AggregateUrlExtractor implements UrlExtractorInterface
{
    /** @var UrlExtractorInterface[] */
    private array $extractors;

    /**
     * @param UrlExtractorInterface[] $extractors
     */
    public function __construct(array $extractors)
    {
        $this->extractors = $extractors;
    }

    /**
     * @return string[]
     */
    public function extractUrls(ParsedProperties $score): array
    {
        return array_reduce(
            $this->extractors,
            fn (array $urls, UrlExtractorInterface $extractor) => array_merge(
                $urls,
                $extractor->extractUrls($score)
            ),
            []
        );
    }
}
