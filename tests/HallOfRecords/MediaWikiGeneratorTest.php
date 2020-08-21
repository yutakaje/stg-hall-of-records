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

use DI\ContainerBuilder;
use Stg\HallOfRecords\MediaWikiGenerator;

class MediaWikiGeneratorTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        $this->execTestWithLocale('en');
    }

    public function testWithJpLocale(): void
    {
        $this->execTestWithLocale('jp');
    }

    private function execTestWithLocale(string $locale): void
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(dirname(dirname(__DIR__)) . '/app/media-wiki.php');
        $container = $builder->build();

        $generator = $container->get(MediaWikiGenerator::class);

        $input = $this->loadFile(__DIR__ . '/media-wiki-generator.input');

        self::assertSame(
            $this->loadFile(__DIR__ . "/media-wiki-generator.output.{$locale}"),
            $generator->generate($input, $locale)
        );
    }
}
