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

namespace Tests;

use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Stg\HallOfRecords\Data\Game\Game;
use Stg\HallOfRecords\Data\Game\Games;
use Stg\HallOfRecords\Data\Score\Score;
use Stg\HallOfRecords\Data\Score\Scores;
use Stg\HallOfRecords\Data\Setting\GameSetting;
use Stg\HallOfRecords\Data\Setting\GlobalSetting;
use Stg\HallOfRecords\Database\Database;
use Tests\Helper\AppHelper;
use Tests\Helper\ContainerHelper;
use Tests\Helper\DatabaseHelper;
use Tests\Helper\FilesystemHelper;
use Tests\Helper\HttpHelper;
use Tests\Helper\LocaleHelper;
use Tests\Helper\LoggingHelper;
use Tests\Helper\MediaWikiHelper;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var \Generator<int> */
    private \Generator $gameIdGenerator;
    /** @var \Generator<int> */
    private \Generator $scoreIdGenerator;

    private ?ContainerInterface $container;
    private ?DatabaseHelper $database;
    private ?FilesystemHelper $filesystem;
    private ?HttpHelper $http;
    private ?LocaleHelper $locale;
    private ?LoggingHelper $logging;
    private ?MediaWikiHelper $mediaWiki;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->gameIdGenerator = $this->createIdGenerator();
        $this->scoreIdGenerator = $this->createIdGenerator();

        // Lazy load everything.
        $this->container = null;
        $this->database = null;
        $this->filesystem = null;
        $this->http = null;
        $this->locale = null;
        $this->logging = null;
        $this->mediaWiki = null;
    }

    private function container(): ContainerInterface
    {
        if ($this->container === null) {
            $this->container = ContainerHelper::createContainer(
                $this->filesystem()->rootDir()
            );
        }

        return $this->container;
    }

    final protected function app(): App
    {
        return AppHelper::createApp($this->container());
    }

    final protected function database(): Database
    {
        if ($this->database === null) {
            $this->database = DatabaseHelper::init($this->container());
        }

        return $this->database->database();
    }

    final protected function filesystem(): FilesystemHelper
    {
        if ($this->filesystem === null) {
            $this->filesystem = new FilesystemHelper();
        }

        return $this->filesystem;
    }

    final protected function http(): HttpHelper
    {
        if ($this->http === null) {
            $this->http = new HttpHelper();
        }

        return $this->http;
    }

    final protected function locale(): LocaleHelper
    {
        if ($this->locale === null) {
            $this->locale = LocaleHelper::init($this->container());
        }

        return $this->locale;
    }

    final protected function logging(): LoggingHelper
    {
        if ($this->logging === null) {
            $this->logging = LoggingHelper::init($this->container());
        }

        return $this->logging;
    }

    final protected function mediaWiki(): MediaWikiHelper
    {
        if ($this->mediaWiki === null) {
            $this->mediaWiki = new MediaWikiHelper($this->filesystem());
        }

        return $this->mediaWiki;
    }

    protected function userAgent(): string
    {
        return 'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/96.0';
    }

    /**
     * @param \Generator<int> $generator
     */
    protected function nextId(\Generator $generator): int
    {
        $value = $generator->current();
        $generator->next();
        return $value;
    }

    /**
     * @return \Generator<int> $generator
     */
    protected function createIdGenerator(): \Generator
    {
        $id = 1;
        while (true) {
            yield $id++;
        }
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createGlobalSetting(array $properties): GlobalSetting
    {
        return new GlobalSetting(
            $properties['name'] ?? '',
            $properties['value'] ?? null
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createGameSetting(array $properties): GameSetting
    {
        return new GameSetting(
            $properties['gameId'] ?? 0,
            $properties['name'] ?? '',
            $properties['value'] ?? null
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createGame(array $properties): Game
    {
        return new Game(
            $properties['id'] ?? $this->nextId($this->gameIdGenerator),
            $properties
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    protected function createScore(array $properties): Score
    {
        return new Score(
            $properties['id'] ?? $this->nextId($this->scoreIdGenerator),
            $properties['gameId'] ?? $this->nextId($this->gameIdGenerator),
            $properties
        );
    }

    protected function loadFile(string $filename): string
    {
        return $this->filesystem()->loadFile($filename);
    }

    protected static function succeed(): void
    {
        self::assertTrue(true);
    }

    /**
     * @param array<string,\Closure(RequestInterface):ResponseInterface> $responseCallbacks
     */
    protected function createHttpClient(array $responseCallbacks): HttpClientInterface
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')
            ->will(self::returnCallback(function (
                RequestInterface $request
            ) use ($responseCallbacks): ResponseInterface {
                $requestUrl = (string)$request->getUri();
                foreach ($responseCallbacks as $url => $responseCallback) {
                    if ($url === $requestUrl) {
                        return $responseCallback($request);
                    }
                }
                self::fail("Response for `{$requestUrl}` does not exist");
            }));

        return $httpClient;
    }

    protected function randomUrl(): string
    {
        return 'https://www.example.org/' . md5(random_bytes(32));
    }
}
