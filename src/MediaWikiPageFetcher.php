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

final class MediaWikiPageFetcher
{
    private string $url;
    /** @var array<string,string> */
    private array $pages;

    /**
     * @param array<string,string> $pages
     */
    public function __construct(string $url, array $pages)
    {
        $this->url = $url;
        $this->pages = $pages;
    }

    public function fetch(string $page): string
    {
        return $this->fetchContents($this->makeUrl($page));
    }

    public function download(string $page): string
    {
        $contents = $this->fetch($page);

        // Not perfect but good enough for now.
        $filename = date('Y-m-d\TH-i_')
            . str_replace('/', '_', $this->pages[$page] ?? $page)
            . '.txt';
        header('Content-Type: text/html; charset=UTF-8');
        header("Content-Transfer-Encoding: Binary");
        header('Content-disposition: attachment; filename="' . $filename . '"');
        echo $contents;
        exit(0);
    }

    private function fetchContents(string $url): string
    {
        $httpClient = new HttpClient();
        try {
            $response = $httpClient->request('GET', $url);
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

    private function makeUrl(string $page): string
    {
        $title = $this->pages[$page] ?? null;

        if ($title === null) {
            throw $this->createException("Invalid page identifier: `{$page}`");
        }

        return "{$this->url}?title=" . urlencode($title) . '&action=edit';
    }

    private function createException(string $message): StgException
    {
        return new StgException("Error fetching input: {$message}");
    }
}
