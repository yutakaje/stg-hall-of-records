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
use Stg\HallOfRecords\Error\StgException;
use Stg\HallOfRecords\Http\HttpContentFetcher;

final class TwitterAccessHeaders
{
    private HttpContentFetcher $httpContentFetcher;

    public function __construct(HttpContentFetcher $httpContentFetcher)
    {
        $this->httpContentFetcher = $httpContentFetcher;
    }

    /**
     * @return array<string,string>
     */
    public function getHeaders(): array
    {
        $accessToken = $this->extractAccessToken(
            $this->fetchMainJs(
                $this->extractMainJsUrl(
                    $this->fetchIndexPage()
                )
            )
        );

        $guestToken = $this->extractGuestToken(
            $this->activateAccessToken($accessToken)
        );

        return [
            'Authorization' => "Bearer {$accessToken}",
            'X-Guest-Token' => $guestToken,
        ];
    }

    private function extractGuestToken(string $content): string
    {
        $pattern = '{"guest_token":"(?<guestToken>[^"]+)"}';

        if (preg_match($pattern, $content, $match) !== 1) {
            throw new StgException('Unable to extract guest token from content');
        }

        return $match['guestToken'];
    }

    private function activateAccessToken(string $accessToken): string
    {
        return $this->httpContentFetcher->fetchContent(
            (new Request('POST', $this->activateAccessTokenUrl()))
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Authorization', "Bearer {$accessToken}")
        );
    }

    private function extractAccessToken(string $mainJs): string
    {
        $pattern = '/s="(?<accessToken>AAAAA[^"]+)"/';

        if (preg_match($pattern, $mainJs, $match) !== 1) {
            throw new StgException('Unable to extract access token from main js');
        }

        return $match['accessToken'];
    }

    private function fetchMainJs(string $url): string
    {
        return $this->httpContentFetcher->fetchContent(
            new Request('GET', $url)
        );
    }

    private function extractMainJsUrl(string $indexPage): string
    {
        if (preg_match($this->mainJsUrlPattern(), $indexPage, $match) !== 1) {
            throw new StgException('Unable to extract main js url from index page');
        }

        return $match['url'];
    }

    private function fetchIndexPage(): string
    {
        return $this->httpContentFetcher->fetchContent(
            new Request('GET', $this->indexPageUrl())
        );
    }

    private function indexPageUrl(): string
    {
        return 'https://twitter.com/';
    }

    private function mainJsUrlPattern(): string
    {
        return '@href="(?<url>https://abs\.twimg\.com/responsive-web/client-web/main\.[^"]+?\.js)"@';
    }

    private function activateAccessTokenUrl(): string
    {
        return 'https://api.twitter.com/1.1/guest/activate.json';
    }
}
