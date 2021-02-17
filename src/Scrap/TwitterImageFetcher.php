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
use Stg\HallOfRecords\Error\StgException;
use Stg\HallOfRecords\Http\HttpContentFetcher;

final class TwitterImageFetcher implements ImageFetcherInterface
{
    private HttpContentFetcher $httpContentFetcher;
    private TwitterAccessHeaders $accessHeaders;

    public function __construct(HttpContentFetcher $httpContentFetcher)
    {
        $this->httpContentFetcher = $httpContentFetcher;
        $this->accessHeaders = new TwitterAccessHeaders($httpContentFetcher);
    }

    public function handles(string $url): bool
    {
        return preg_match($this->tweetUrlPattern(), $url) === 1;
    }

    /**
     * @return ResponseInterface[]
     */
    public function fetch(string $url): array
    {
        if (!$this->handles($url)) {
            throw new StgException("Fetcher cannot handle url: `{$url}`");
        }

        return array_map(
            fn (string $url) => $this->fetchImage($url),
            $this->extractImageUrls($url)
        );
    }

    private function fetchImage(string $url): ResponseInterface
    {
        if (!$this->handlesImage($url)) {
            throw new StgException("Fetcher cannot handle image url: `{$url}`");
        }

        $response = $this->httpContentFetcher->sendRequest(
            new Request('GET', $url)
        );

        if ($response->getStatusCode() !== 200) {
            throw new ImageNotFoundException("Image not found at url `{$url}`");
        }

        return $response;
    }

    /**
     * @return string[]
     */
    private function extractImageUrls(string $url): array
    {
        $headers = $this->accessHeaders->getHeaders();

        $tweetId = $this->extractTweetId($url);
        $tweet = $this->getTweet($tweetId, $headers);

        $media = $tweet->entities->media ?? null;

        if ($media === null) {
            throw new StgException("No media detected for tweet id `{$tweetId}`");
        }

        return array_map(
            // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
            fn (\stdClass $entry) => $entry->media_url_https,
            $media
        );
    }

    /**
     * @param array<string,string> $accessHeaders
     */
    private function getTweet(string $tweetId, array $accessHeaders): \stdClass
    {
        $request = new Request('GET', $this->conversationUrl($tweetId));

        foreach ($accessHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $json = $this->httpContentFetcher->fetchContent($request->withUri(
            $request->getUri()->withQuery(implode('&', [
                'include_profile_interstitial_type=1',
                'include_blocking=1',
                'include_blocked_by=1',
                'include_followed_by=1',
                'include_want_retweets=1',
                'include_mute_edge=1',
                'include_can_dm=1',
                'include_can_media_tag=1',
                'skip_status=1',
                'cards_platform=Web-12',
                'include_cards=1',
                'include_ext_alt_text=true',
                'include_quote_count=true',
                'include_reply_count=1',
                'tweet_mode=extended',
                'include_entities=true',
                'include_user_entities=true',
                'include_ext_media_color=true',
                'include_ext_media_availability=true',
                'send_error_codes=true',
                'simple_quoted_tweet=true',
                'count=20',
                'include_ext_has_birdwatch_notes=false',
                'ext=mediaStats%2ChighlightedLabel',
            ]))
        ));

        try {
            $conversation = json_decode($json, false, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new StgException(
                "Error decoding json for tweet id `{$tweetId}`"
                . ": `{$exception->getMessage()}`"
            );
        }

        $tweets = $conversation->globalObjects->tweets ?? null;
        if (!($tweets instanceof \stdClass)) {
            throw new StgException(
                "Unknown structure detected for tweet id `{$tweetId}` (I)"
            );
        }

        $tweet = $tweets->{$tweetId} ?? null;

        if (!($tweet instanceof \stdClass)) {
            throw new StgException(
                "Unknown structure detected for tweet id `{$tweetId}`"
            );
        }

        return $tweet;
    }

    private function extractTweetId(string $url): string
    {
        if (preg_match($this->tweetUrlPattern(), $url, $match) !== 1) {
            throw new StgException("Unable to get tweet id from url: `{$url}`");
        }

        return $match['tweetId'];
    }

    private function handlesImage(string $url): bool
    {
        return preg_match($this->imageUrlPattern(), $url) === 1;
    }

    private function conversationUrl(string $tweetId): string
    {
        return "https://api.twitter.com/2/timeline/conversation/{$tweetId}.json";
    }

    private function tweetUrlPattern(): string
    {
        return '@https://twitter.com/[^/]+/status/(?<tweetId>[0-9]+)@';
    }

    private function imageUrlPattern(): string
    {
        return '@https://pbs.twimg.com/media/[^\.]+\.[a-zA-Z0-9]+@';
    }
}