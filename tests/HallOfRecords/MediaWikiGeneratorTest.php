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

namespace Tests\HallOfRecords;

use Stg\HallOfRecords\Database\ConnectionFactory;
use Stg\HallOfRecords\Database\InMemoryDatabaseCreator;
use Stg\HallOfRecords\Database\RepositoryFactory;
use Stg\HallOfRecords\MediaWikiGenerator;

class MediaWikiGeneratorTest extends \Tests\TestCase
{
    public function testWithLocales(): void
    {
        $generator = new MediaWikiGenerator(
            new InMemoryDatabaseCreator(
                new ConnectionFactory()
            ),
            new RepositoryFactory()
        );

        $input = $this->loadFile(__DIR__ . '/media-wiki-input');

        self::assertSame(
            $this->loadFile(__DIR__ . '/media-wiki-output-en'),
            $generator->generate($input, 'en')
        );
        self::assertSame(
            $this->loadFile(__DIR__ . '/media-wiki-output-jp'),
            $generator->generate($input, 'jp')
        );
    }
}
