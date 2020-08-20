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

use Stg\HallOfRecords\Data\GameRepository;
use Stg\HallOfRecords\Data\GameRepositoryInterface;
use Stg\HallOfRecords\Data\ScoreRepository;
use Stg\HallOfRecords\Data\ScoreRepositoryInterface;
use Stg\HallOfRecords\Export\MediaWikiExporter;
use Stg\HallOfRecords\Import\MediaWikiImporter;
use Stg\HallOfRecords\Import\MediaWiki\YamlExtractor;
use Stg\HallOfRecords\Import\YamlParser;
use Stg\HallOfRecords\MediaWikiGenerator;

return [
    GameRepositoryInterface::class => DI\create(GameRepository::class),
    ScoreRepositoryInterface::class => DI\create(ScoreRepository::class),

    MediaWikiImporter::class => DI\autowire(),
    YamlExtractor::class => DI\create(),
    YamlParser::class => DI\create(),

    MediaWikiExporter::class => DI\autowire(),

    MediaWikiGenerator::class => DI\autowire(),
];
