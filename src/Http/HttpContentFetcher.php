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

namespace Stg\HallOfRecords\Http;

use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Stg\HallOfRecords\Error\StgException;

final class HttpContentFetcher
{
    private HttpClientInterface $httpClient;
    private string $userAgent;

    public function __construct(
        HttpClientInterface $httpClient,
        string $userAgent
    ) {
        $this->httpClient = $httpClient;
        $this->userAgent = $userAgent;
    }

    public function fetchContent(RequestInterface $request): string
    {
        $response = $this->sendRequest($request);

        if ($response->getStatusCode() !== 200) {
            throw new StgException(
                "Error fetching content from url `{$request->getUri()}`"
            );
        }

        return (string)$response->getBody();
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->httpClient->sendRequest(
                $request->withHeader('User-Agent', $this->userAgent)
            );
        } catch (RequestExceptionInterface $exception) {
            throw new StgException(
                "Error sending request: {$exception->getMessage()}"
            );
        }
    }
}
