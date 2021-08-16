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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;
use Stg\HallOfRecords\Database\Database;
use Stg\HallOfRecords\Shared\Application\Query\ListQueryCreator;
use Stg\HallOfRecords\Shared\Application\Query\ViewQueryCreator;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\LocaleDir;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\LocaleNegotiator;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Translator;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\TranslatorInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\MediaWiki\BasicTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;

return [
    'routes' => [],
    'middleware' => [],

    'settings' => require __DIR__ . '/settings.php',

    LoggerInterface::class => DI\factory(function (
        array $settings
    ): LoggerInterface {
        $settings = $settings['logger'];
        $logger = new Logger($settings['name']);
        $logger->pushProcessor(new UidProcessor());
        $logger->pushHandler(new RotatingFileHandler(
            $settings['path'],
            $settings['numFiles'],
            $settings['level']
        ));
        return $logger;
    })->parameter('settings', DI\get('settings')),

    Database::class => DI\autowire(),
    Connection::class => DI\factory(function (array $settings): Connection {
        return DriverManager::getConnection($settings['database']);
    })->parameter('settings', DI\get('settings')),

    Locales::class => function (): Locales {
        return new Locales('en', [
            new Locale('en'),
            new Locale('ja'),
        ]);
    },
    LocaleDir::class => function (): LocaleDir {
        return new LocaleDir(dirname(__DIR__) . '/locale');
    },
    LocaleNegotiator::class => DI\autowire(),
    TranslatorInterface::class => DI\autowire(Translator::class),

    ListQueryCreator::class => DI\autowire(),
    ViewQueryCreator::class => DI\autowire(),

    Routes::class => DI\autowire(),
    BasicTemplate::class => DI\autowire(),
];
