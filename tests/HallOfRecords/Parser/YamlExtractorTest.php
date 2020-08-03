<?php

declare(strict_types=1);

/*
 * This file is part of the stg/hall-of-records package.
n *
 * (c) YTK <yutakaje@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\HallOfRecords\Parser;

use Stg\HallOfRecords\Parser\YamlExtractor;
use Symfony\Component\Yaml\Yaml;

class YamlExtractorTest extends \Tests\TestCase
{
    public function testWithValidInput(): void
    {
        $input = $this->loadFile('wiki-input');
        $expected = $this->loadFile('yaml-output');

        $extractor = new YamlExtractor($input);

        self::assertSame(
            $extractor->extract(),
            array_map(
                fn (string $yaml) => Yaml::parse($yaml),
                explode('<<<<<<<<<<==========>>>>>>>>>>', $expected)
            )
        );
    }

    private function loadFile(string $filename): string
    {
        $contents = file_get_contents(__DIR__ . "/{$filename}");

        if ($contents === false) {
            throw new \UnexpectedValueException(
                "Unable to load file: `{$filename}`"
            );
        }

        return $contents;
    }
}
