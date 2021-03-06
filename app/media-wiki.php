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

use GuzzleHttp\Client as HttpClient;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Log\LoggerInterface;
use Stg\HallOfRecords\Data\Game\GameRepository;
use Stg\HallOfRecords\Data\Game\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Score\ScoreRepository;
use Stg\HallOfRecords\Data\Score\ScoreRepositoryInterface;
use Stg\HallOfRecords\Data\Setting\SettingRepository;
use Stg\HallOfRecords\Data\Setting\SettingRepositoryInterface;
use Stg\HallOfRecords\Export\MediaWikiExporter;
use Stg\HallOfRecords\Http\HttpContentFetcher;
use Stg\HallOfRecords\Import\MediaWikiImporter;
use Stg\HallOfRecords\Import\MediaWiki\YamlExtractor;
use Stg\HallOfRecords\Import\MediaWiki\YamlParser;
use Stg\HallOfRecords\MediaWikiDatabaseFilter;
use Stg\HallOfRecords\MediaWikiGenerator;
use Stg\HallOfRecords\MediaWikiImageScraper;
use Stg\HallOfRecords\MediaWikiPageFetcher;
use Stg\HallOfRecords\Scrap\DefaultImageFetcher;
use Stg\HallOfRecords\Scrap\Extract\AggregateUrlExtractor;
use Stg\HallOfRecords\Scrap\Extract\DefaultUrlExtractor;
use Stg\HallOfRecords\Scrap\Extract\UrlExtractorInterface;
use Stg\HallOfRecords\Scrap\Extract\TwitterUrlExtractor;
use Stg\HallOfRecords\Scrap\ImageFetcherDirector;
use Stg\HallOfRecords\Scrap\ImageFetcherInterface;
use Stg\HallOfRecords\Scrap\TwitterImageFetcher;

return [
    'wiki-url' => 'https://shmups.wiki',
    'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/96.0',

    'save-path' => static function (): string {
        return dirname(__DIR__) . '/public/image-scraper/save';
    },
    'save-url' => static function (ContainerInterface $container): string {
        return $container->get('wiki-url') . '/records/image-scraper/save';
    },

    SettingRepositoryInterface::class => DI\create(SettingRepository::class),
    GameRepositoryInterface::class => DI\create(GameRepository::class),
    ScoreRepositoryInterface::class => DI\create(ScoreRepository::class),

    MediaWikiImporter::class => DI\autowire(),
    YamlExtractor::class => DI\create(),
    YamlParser::class => DI\create(),

    MediaWikiExporter::class => DI\autowire(),

    MediaWikiPageFetcher::class => DI\create()->constructor(
        DI\get(HttpClientInterface::class),
        DI\get('wiki-url'),
        [
            'database' => 'STG_Hall_of_Records/Database',
            'page-en' => 'STG_Hall_of_Records',
        ]
    ),
    MediaWikiDatabaseFilter::class => DI\autowire(),
    MediaWikiGenerator::class => DI\autowire(),
    MediaWikiImageScraper::class => DI\autowire(),

    UrlExtractorInterface::class => DI\get(AggregateUrlExtractor::class),
    AggregateUrlExtractor::class => DI\create()->constructor([
        DI\get(TwitterUrlExtractor::class),
        DI\get(DefaultUrlExtractor::class),
    ]),
    DefaultUrlExtractor::class => DI\autowire(),
    TwitterUrlExtractor::class => DI\autowire(),

    ImageFetcherInterface::class => DI\get(ImageFetcherDirector::class),
    ImageFetcherDirector::class => DI\create()->constructor([
        DI\get(TwitterImageFetcher::class),
        DI\get(DefaultImageFetcher::class),
    ]),
    DefaultImageFetcher::class => DI\autowire(),
    TwitterImageFetcher::class => DI\autowire(),

    HttpClientInterface::class => DI\create(HttpClient::class),
    HttpContentFetcher::class => DI\create()->constructor(
        DI\get(HttpClientInterface::class),
        DI\get('user-agent')
    ),

    LoggerInterface::class => static function (): Logger {
        $logger = new Logger('stg-hall-of-records');
        $logger->pushProcessor(new UidProcessor());
        $logger->pushHandler(new RotatingFileHandler(
            dirname(__DIR__) . '/logs/app.log',
            30,
            Logger::DEBUG
        ));
        return $logger;
    },
];
