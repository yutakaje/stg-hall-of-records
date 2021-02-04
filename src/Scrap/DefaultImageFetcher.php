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
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Stg\HallOfRecords\Error\StgException;

final class DefaultImageFetcher implements ImageFetcherInterface
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function handles(string $url): bool
    {
        return true;
    }

    public function fetch(string $url): ResponseInterface
    {
        $response = $this->sendRequest($url);

        if ($response->getStatusCode() !== 200) {
            throw new ImageNotFoundException("Image not found at url `{$url}`");
        }

        return $response;
    }

    private function sendRequest(string $url): ResponseInterface
    {
        try {
            return $this->httpClient->sendRequest(
                new Request('GET', $url)
            );
        } catch (RequestExceptionInterface $exception) {
            throw new StgException("Error fetching image: {$exception->getMessage()}");
        }
    }
}
