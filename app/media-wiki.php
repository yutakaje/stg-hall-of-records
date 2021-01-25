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
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Log\LoggerInterface;
use Stg\HallOfRecords\Data\Game\GameRepository;
use Stg\HallOfRecords\Data\Game\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Score\ScoreRepository;
use Stg\HallOfRecords\Data\Score\ScoreRepositoryInterface;
use Stg\HallOfRecords\Data\Setting\SettingRepository;
use Stg\HallOfRecords\Data\Setting\SettingRepositoryInterface;
use Stg\HallOfRecords\Export\MediaWikiExporter;
use Stg\HallOfRecords\Import\MediaWikiImporter;
use Stg\HallOfRecords\Import\MediaWiki\YamlExtractor;
use Stg\HallOfRecords\Import\MediaWiki\YamlParser;
use Stg\HallOfRecords\MediaWikiDatabaseFilter;
use Stg\HallOfRecords\MediaWikiGenerator;
use Stg\HallOfRecords\MediaWikiPageFetcher;

return [
    SettingRepositoryInterface::class => DI\create(SettingRepository::class),
    GameRepositoryInterface::class => DI\create(GameRepository::class),
    ScoreRepositoryInterface::class => DI\create(ScoreRepository::class),

    MediaWikiImporter::class => DI\autowire(),
    YamlExtractor::class => DI\create(),
    YamlParser::class => DI\create(),

    MediaWikiExporter::class => DI\autowire(),

    MediaWikiPageFetcher::class => DI\create()->constructor(
        DI\get(HttpClientInterface::class),
        'https://shmups.wiki',
        [
            'database' => 'STG_Hall_of_Records/Database',
            'page-en' => 'STG_Hall_of_Records',
        ]
    ),
    MediaWikiDatabaseFilter::class => DI\autowire(),
    MediaWikiGenerator::class => DI\autowire(),

    HttpClientInterface::class => DI\create(HttpClient::class),

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
