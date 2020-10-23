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

namespace Stg\HallOfRecords;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Stg\HallOfRecords\Error\StgException;

final class MediaWikiDatabaseFetcher
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function fetch(): string
    {
        $httpClient = new HttpClient();
        try {
            $response = $httpClient->request('GET', $this->url);
        } catch (ClientException $exception) {
            throw $this->createException($exception->getMessage());
        }

        if ($response->getStatusCode() !== 200) {
            throw $this->createException(
                'Error retrieving database contents from URL'
            );
        }

        $body = (string)$response->getBody();

        $startPos = strpos($body, 'name="wpTextbox1">');
        if ($startPos === false) {
            throw $this->createException(
                'Error calculating start position within wiki contents'
            );
        }
        $startPos += 18;

        $endPos = strpos($body, '</textarea>', $startPos);
        if ($endPos === false) {
            throw $this->createException(
                'Error calculating end position within wiki contents'
            );
        }

        return html_entity_decode(substr($body, $startPos, $endPos - $startPos));
    }

    private function createException(string $message): StgException
    {
        return new StgException("Error fetching input: {$message}");
    }
}
