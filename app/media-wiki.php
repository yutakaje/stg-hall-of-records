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
use Stg\HallOfRecords\Database\ConnectionFactory;
use Stg\HallOfRecords\Database\GameRepository;
use Stg\HallOfRecords\Database\InMemoryDatabaseCreator;
use Stg\HallOfRecords\Database\ParsedDataWriter;
use Stg\HallOfRecords\Database\ScoreRepository;
use Stg\HallOfRecords\Export\MediaWikiExporter;
use Stg\HallOfRecords\Import\YamlExtractor;
use Stg\HallOfRecords\Import\YamlParser;
use Stg\HallOfRecords\MediaWikiGenerator;

return [
    YamlExtractor::class => DI\create(),
    YamlParser::class => DI\create(),

    ConnectionFactory::class => DI\autowire(),
    InMemoryDatabaseCreator::class => DI\autowire(),
    Connection::class => static function (
        ConnectionFactory $connectionFactory
    ): Connection {
        $connection = $connectionFactory->create();

        $databaseCreator = new InMemoryDatabaseCreator($connection);
        $databaseCreator->create();

        return $connection;
    },
    ParsedDataWriter::class => DI\autowire(),

    GameRepository::class => DI\autowire(),
    ScoreRepository::class => DI\autowire(),

    MediaWikiExporter::class => DI\autowire(),

    MediaWikiGenerator::class => DI\autowire(),
];
