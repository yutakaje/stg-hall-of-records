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

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Stg\HallOfRecords\Http\HttpContentFetcher;
use Stg\HallOfRecords\Error\StgException;

final class DefaultImageFetcher implements ImageFetcherInterface
{
    private HttpContentFetcher $httpContentFetcher;
    /** @var string[] */
    private array $excludePatterns;

    /**
     * @param string[] $excludePatterns
     */
    public function __construct(
        HttpContentFetcher $httpContentFetcher,
        array $excludePatterns = []
    ) {
        $this->httpContentFetcher = $httpContentFetcher;
        $this->excludePatterns = $excludePatterns;
    }

    public function handles(string $url): bool
    {
        foreach ($this->excludePatterns as $pattern) {
            if (preg_match($pattern, $url) === 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return ResponseInterface[]
     */
    public function fetch(string $url): array
    {
        if (!$this->handles($url)) {
            throw new StgException("Fetcher cannot handle url: `{$url}`");
        }

        $response = $this->sendRequest($url);

        if ($response->getStatusCode() !== 200) {
            throw new ImageNotFoundException("Image not found at url `{$url}`");
        }

        return [$response];
    }

    private function sendRequest(string $url): ResponseInterface
    {
        return $this->httpContentFetcher->sendRequest(
            new Request('GET', $url)
        );
    }
}
