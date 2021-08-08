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

namespace Tests\Helper;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

final class HttpHelper
{
    private ServerRequestFactory $serverRequestFactory;
    private ResponseFactory $responseFactory;

    public function __construct()
    {
        $this->serverRequestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    /**
     * @param array<string,mixed> $serverParams
     */
    public function createServerRequest(
        string $method,
        string $uri,
        array $serverParams = []
    ): ServerRequestInterface {
        return $this->serverRequestFactory->createServerRequest(
            $method,
            $uri,
            $serverParams
        );
    }

    public function createResponse(
        int $statusCode = StatusCodeInterface::STATUS_OK,
        string $reasonPhrase = ''
    ): ResponseInterface {
        return $this->responseFactory->createResponse($statusCode, $reasonPhrase);
    }

    public function replaceInUriPath(
        ServerRequestInterface $request,
        string $search,
        string $replace
    ): ServerRequestInterface {
        $uri = $request->getUri();

        return $request->withUri(
            $uri->withPath(str_replace(
                rawurlencode($search),
                rawurlencode($replace),
                $uri->getPath()
            ))
        );
    }
}
