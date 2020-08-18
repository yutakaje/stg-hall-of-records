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

use Stg\HallOfRecords\Database\ConnectionFactory;
use Stg\HallOfRecords\Database\InMemoryDatabaseCreator;
use Stg\HallOfRecords\Database\RepositoryFactory;
use Stg\HallOfRecords\MediaWikiGenerator;

return [
    ConnectionFactory::class => DI\create(),
    InMemoryDatabaseCreator::class => DI\autowire(),
    RepositoryFactory::class => DI\create(),

    MediaWikiGenerator::class => DI\autowire(),
];
