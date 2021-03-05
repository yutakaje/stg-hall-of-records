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

use Psr\Http\Message\ResponseInterface;
use Stg\HallOfRecords\Import\MediaWiki\ParsedProperties;
use Stg\HallOfRecords\Import\MediaWiki\YamlExtractor;
use Stg\HallOfRecords\Import\MediaWiki\YamlParser;
use Stg\HallOfRecords\Scrap\Extract\UrlExtractorInterface;
use Stg\HallOfRecords\Scrap\ImageFetcherException;
use Stg\HallOfRecords\Scrap\ImageFetcherInterface;
use Stg\HallOfRecords\Scrap\Message;
use Stg\HallOfRecords\Scrap\MessageHandler;

final class MediaWikiImageScraper
{
    private const MSG_TYPE_INFO = 'info';
    private const MSG_TYPE_SUCCESS = 'success';
    private const MSG_TYPE_ERROR = 'error';

    private const MSG_SCRAP_GAME = 'Scrapping from game';
    private const MSG_GAME_SCRAPPED = 'Scrapped from game';
    private const MSG_SCRAP_SCORE = 'Scrapping from score';
    private const MSG_SCORE_SCRAPPED = 'Scrapped from score';
    private const MSG_FETCH_IMAGE = 'Fetching image';
    private const MSG_IMAGE_NOT_FOUND = 'Image not found';
    private const MSG_IMAGE_FETCHED = 'Image fetched';
    private const MSG_IMAGE_ALREADY_EXISTS = 'Image already exists';
    private const MSG_IMAGE_SAVED = 'Image saved';
    private const MSG_RUNTIME_LIMIT_REACHED = 'Runtime limit reached';
    private const MSG_SCRAPPING_STARTED = 'Scrapping started';
    private const MSG_SCRAPPING_COMPLETE = 'Scrapping complete';

    private MediaWikiPageFetcher $pageFetcher;
    private UrlExtractorInterface $urlExtractor;
    private ImageFetcherInterface $imageFetcher;
    private MessageHandler $messageHandler;
    private string $savePath;
    /** Unit for time related properties is seconds */
    private float $runtimeLimit;
    private float $startTime;
    private float $elapsedTime;

    public function __construct(
        MediaWikiPageFetcher $pageFetcher,
        UrlExtractorInterface $urlExtractor,
        ImageFetcherInterface $imageFetcher
    ) {
        $this->pageFetcher = $pageFetcher;
        $this->urlExtractor = $urlExtractor;
        $this->imageFetcher = $imageFetcher;
        $this->messageHandler = new MessageHandler();
        $this->savePath = '';
        $this->runtimeLimit = 0.0;
        $this->startTime = 0.0;
        $this->elapsedTime = 0.0;
    }

    /**
     * @return Message[]
     */
    public function getMessages(): array
    {
        return $this->filterEmptyMessageBlocks(
            $this->messageHandler->all()
        );
    }

    public function getElapsedTime(): float
    {
        return $this->elapsedTime;
    }

    public function scrap(string $path, float $runtimeLimit = 0.0): void
    {
        $this->useSavePath($path);
        $this->useRuntimeLimit($runtimeLimit);
        $this->messageHandler->reset();

        $data = $this->parse(
            $this->pageFetcher->fetch('database')
        );

        $this->startTimer();
        $this->scrapImagesFromGames($data->get('games', []));
        $this->stopTimer();
    }

    /**
     * @param ParsedProperties[] $games
     */
    private function scrapImagesFromGames(array $games): void
    {
        $this->addInfoMessage(self::MSG_SCRAPPING_STARTED);

        foreach ($games as $game) {
            try {
                $this->messageHandler->addContext('game', $this->gameIdentifier(
                    $game
                ));
                $this->addInfoMessage(self::MSG_SCRAP_GAME);

                $this->scrapImagesFromGame($game);

                $this->addInfoMessage(self::MSG_GAME_SCRAPPED);
            } finally {
                $this->messageHandler->removeContext('game');
            }

            if ($this->isRuntimeLimitReached()) {
                $this->addInfoMessage(self::MSG_RUNTIME_LIMIT_REACHED);
                return;
            }
        }

        $this->addInfoMessage(self::MSG_SCRAPPING_COMPLETE);
    }

    private function scrapImagesFromGame(ParsedProperties $game): void
    {
        foreach ($game->get('scores', []) as $score) {
            try {
                $this->messageHandler->addContext('score', $this->scoreIdentifier(
                    $game,
                    $score
                ));
                $this->addInfoMessage(self::MSG_SCRAP_SCORE);

                $this->scrapImagesFromScore($game, $score);

                $this->addInfoMessage(self::MSG_SCORE_SCRAPPED);
            } finally {
                $this->messageHandler->removeContext('score');
            }

            if ($this->isRuntimeLimitReached()) {
                return;
            }
        }
    }

    private function scrapImagesFromScore(
        ParsedProperties $game,
        ParsedProperties $score
    ): void {
        $this->saveImages(
            $game,
            $score,
            $this->scrapImages(
                $game,
                $score,
                $this->urlExtractor->extractUrls($score)
            )
        );
    }

    /**
     * @param string[] $urls
     * @return \stdClass[]
     */
    private function scrapImages(
        ParsedProperties $game,
        ParsedProperties $score,
        array $urls
    ): array {
        $images = [];

        foreach ($urls as $url) {
            try {
                $this->messageHandler->addContext('url', $url);

                $images[] = $this->scrapImage($game, $score, $url);
            } finally {
                $this->messageHandler->removeContext('url');
            }
        }

        return array_filter($images);
    }

    private function scrapImage(
        ParsedProperties $game,
        ParsedProperties $score,
        string $url
    ): ?\stdClass {
        $imageId = $this->makeImageId($game, $score, $url);

        try {
            $this->messageHandler->addContext('image', $imageId);

            if ($this->imageExists($imageId)) {
                $this->addInfoMessage(self::MSG_IMAGE_ALREADY_EXISTS);
                return null;
            }

            $this->addInfoMessage(self::MSG_FETCH_IMAGE);
            $responses = $this->fetchImages($url);
            $this->addInfoMessage(self::MSG_IMAGE_FETCHED);

            return $this->createImage($imageId, $url, $responses);
        } catch (ImageFetcherException $exception) {
            $this->addErrorMessage(self::MSG_IMAGE_NOT_FOUND, [
                'error' => $exception->getMessage(),
            ]);
            return null;
        } finally {
            $this->messageHandler->removeContext('image');
        }
    }

    /**
     * @param ResponseInterface[] $responses
     */
    private function createImage(
        string $imageId,
        string $url,
        array $responses
    ): \stdClass {
        $image = new \stdClass();
        $image->id = $imageId;
        $image->url = $url;
        $image->files = array_map(
            function (ResponseInterface $response): \stdClass {
                $file = new \stdClass();
                $file->mimeType = $response->getHeaderLine('Content-Type');
                $file->payload = (string)$response->getBody();
                return $file;
            },
            $responses
        );
        return $image;
    }

    /**
     * @return ResponseInterface[]
     */
    private function fetchImages(string $url): array
    {
        return $this->imageFetcher->fetch($url);
    }

    /**
     * @param \stdClass[] $images
     */
    private function saveImages(
        ParsedProperties $game,
        ParsedProperties $score,
        array $images
    ): void {
        foreach ($images as $image) {
            try {
                $this->messageHandler->addContext('image', $image->id);

                $this->saveImage($image);
            } finally {
                $this->messageHandler->removeContext('image');
            }
        }
    }

    private function saveImage(\stdClass $image): void
    {
        $dir = "{$this->savePath}/{$image->id}";
        mkdir($dir, 0777, true);

        $numFiles = sizeof($image->files);
        foreach ($image->files as $index => $file) {
            $file->filename = $this->getImageFilename($file, $numFiles, $index);
            file_put_contents("{$dir}/{$file->filename}", $file->payload);
        }

        file_put_contents(
            "{$dir}/meta.json",
            json_encode(
                [
                    'id' => $image->id,
                    'url' => $image->url,
                    'files' => array_map(
                        fn (\stdClass $file) => [
                            'filename' => $file->filename,
                            'mimeType' => $file->mimeType,
                        ],
                        $image->files
                    ),
                ],
                JSON_PRETTY_PRINT
            )
        );

        $this->addSuccessMessage(self::MSG_IMAGE_SAVED);
    }

    private function getImageFilename(
        \stdClass $file,
        int $numFiles,
        int $index
    ): string {
        $filename = $numFiles > 1 ? 'image-' . ($index + 1) : 'image';
        return $filename . $this->getImageExtension($file->mimeType);
    }

    private function getImageExtension(string $mimeType): string
    {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return '.jpg';
            case 'image/png':
                return '.png';
            case 'image/gif':
                return '.gif';
            default:
                return '';
        }
    }

    private function imageExists(string $imageId): bool
    {
        return file_exists("{$this->savePath}/{$imageId}");
    }

    private function gameIdentifier(ParsedProperties $game): string
    {
        return urlencode(
            str_replace(' ', '_', strtolower($game->get('name', '')))
        );
    }

    private function scoreIdentifier(
        ParsedProperties $game,
        ParsedProperties $score
    ): string {
        $score = preg_replace('/[^\d]/', '', $score->get('score', ''));

        return $this->gameIdentifier($game) . '/' . urlencode($score);
    }

    private function makeImageId(
        ParsedProperties $game,
        ParsedProperties $score,
        string $url
    ): string {
        return $this->scoreIdentifier($game, $score) . '_' . md5($url);
    }

    private function parse(string $input): ParsedProperties
    {
        return $this->parseYaml(
            $this->extractYaml($input)
        );
    }

    /**
     * @return array[]
     */
    private function extractYaml(string $input): array
    {
        $extractor = new YamlExtractor();
        return $extractor->extract($input);
    }

    /**
     * @param array<string,mixed>[] $sections
     */
    private function parseYaml(array $sections): ParsedProperties
    {
        $parser = new YamlParser();
        return $parser->parse($sections);
    }

    /**
     * @param Message[] $messages
     * @return Message[]
     */
    private function filterEmptyMessageBlocks(array $messages): array
    {
        return $this->messageHandler->filterEmptyBlocks(
            $this->messageHandler->filterEmptyBlocks(
                $messages,
                self::MSG_SCRAP_SCORE,
                self::MSG_SCORE_SCRAPPED
            ),
            self::MSG_SCRAP_GAME,
            self::MSG_GAME_SCRAPPED
        );
    }

    private function useSavePath(string $path): void
    {
        $this->savePath = $path;
    }

    private function useRuntimeLimit(float $runtimeLimit): void
    {
        $this->runtimeLimit = $runtimeLimit;
    }

    private function isRuntimeLimitReached(): bool
    {
        if ($this->runtimeLimit < 0.1) {
            return false;
        }

        return microtime(true) - $this->startTime > $this->runtimeLimit;
    }

    private function startTimer(): void
    {
        $this->startTime = microtime(true);
        $this->elapsedTime = 0.0;
    }

    private function stopTimer(): void
    {
        $this->elapsedTime = microtime(true) - $this->startTime;
        $this->startTime = 0.0;
    }

    /**
     * @param array<string,mixed> $context
     */
    private function addInfoMessage(string $message, array $context = []): void
    {
        $this->addMessage(self::MSG_TYPE_INFO, $message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    private function addSuccessMessage(string $message, array $context = []): void
    {
        $this->addMessage(self::MSG_TYPE_SUCCESS, $message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    private function addErrorMessage(string $message, array $context = []): void
    {
        $this->addMessage(self::MSG_TYPE_ERROR, $message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    private function addMessage(
        string $type,
        string $message,
        array $context = []
    ): void {
        $this->messageHandler->addMessage($message, [
            'type' => $type,
        ] + $context);
    }
}
