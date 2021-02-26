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

namespace Stg\HallOfRecords\Scrap;

use Psr\Http\Message\ResponseInterface;
use Stg\HallOfRecords\Error\StgException;

final class ImageFetcherDirector implements ImageFetcherInterface
{
    /** @var ImageFetcherInterface[] */
    private array $imageFetchers;

    /**
     * @param ImageFetcherInterface[] $imageFetchers
     */
    public function __construct(array $imageFetchers)
    {
        $this->imageFetchers = $imageFetchers;
    }

    public function handles(string $url): bool
    {
        foreach ($this->imageFetchers as $imageFetcher) {
            if ($imageFetcher->handles($url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ResponseInterface[]
     */
    public function fetch(string $url): array
    {
        foreach ($this->imageFetchers as $imageFetcher) {
            if ($imageFetcher->handles($url)) {
                return $imageFetcher->fetch($url);
            }
        }

        throw new StgException("No image fetcher found for url `{$url}`");
    }
}
