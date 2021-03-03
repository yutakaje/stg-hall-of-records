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

final class TwitterUrlExtractor implements UrlExtractorInterface
{
    /**
     * @return string[]
     */
    public function extractUrls(ParsedProperties $score): array
    {
        return $this->getTwitterUrls(
            $this->getTwitterSources($score)
        );
    }

    /**
     * @return ParsedProperties[]
     */
    private function getTwitterSources(ParsedProperties $score): array
    {
        return array_values(array_filter(
            $score->get('sources', []),
            fn (ParsedProperties $source) => $source->get('name') === 'Twitter'
        ));
    }

    /**
     * @param ParsedProperties[] $sources
     * @return string[]
     */
    private function getTwitterUrls(array $sources): array
    {
        return array_values(array_filter(
            array_map(
                fn (ParsedProperties $source) => $source->get('url'),
                $sources
            )
        ));
    }
}
