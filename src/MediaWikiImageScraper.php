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
use Stg\HallOfRecords\Error\StgException;
use Stg\HallOfRecords\Import\MediaWiki\ParsedProperties;
use Stg\HallOfRecords\Import\MediaWiki\YamlExtractor;
use Stg\HallOfRecords\Import\MediaWiki\YamlParser;
use Stg\HallOfRecords\Scrap\ImageFetcherInterface;
use Stg\HallOfRecords\Scrap\ImageNotFoundException;
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

    private MediaWikiPageFetcher $pageFetcher;
    private ImageFetcherInterface $imageFetcher;
    private MessageHandler $messageHandler;
    private string $savePath;

    public function __construct(
        MediaWikiPageFetcher $pageFetcher,
        ImageFetcherInterface $imageFetcher
    ) {
        $this->pageFetcher = $pageFetcher;
        $this->imageFetcher = $imageFetcher;
        $this->messageHandler = new MessageHandler();
        $this->savePath = '';
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

    public function scrap(string $path): void
    {
        $this->useSavePath($path);
        $this->messageHandler->reset();

        $data = $this->parse(
            $this->pageFetcher->fetch('database')
        );

        $this->scrapImagesFromGames($data->get('games', []));
    }

    /**
     * @param ParsedProperties[] $games
     */
    private function scrapImagesFromGames(array $games): void
    {
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
        }
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
                $this->extractUrlsFromScore($score)
            )
        );
    }

    /**
     * @return string[]
     */
    private function extractUrlsFromScore(ParsedProperties $score): array
    {
        return $score->get('image-urls', []);
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
            } catch (ImageNotFoundException $exception) {
                $this->addErrorMessage(self::MSG_IMAGE_NOT_FOUND);
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

        if ($this->imageExists($imageId)) {
            $this->addInfoMessage(self::MSG_IMAGE_ALREADY_EXISTS, [
                'image' => $imageId,
            ]);
            return null;
        }

        $this->addInfoMessage(self::MSG_FETCH_IMAGE);
        $responses = $this->fetchImages($url);
        $this->addInfoMessage(self::MSG_IMAGE_FETCHED);

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

    private function createException(string $message): StgException
    {
        return new StgException("Error scrapping image: {$message}");
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
