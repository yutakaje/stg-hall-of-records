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

namespace Tests\HallOfRecords\Import\MediaWiki;

use Stg\HallOfRecords\Error\StgException;
use Stg\HallOfRecords\Import\MediaWiki\YamlExtractor;
use Symfony\Component\Yaml\Yaml;

class YamlExtractorTest extends \Tests\TestCase
{
    public function testWithValidInput(): void
    {
        $input = $this->loadFile(__DIR__ . '/extractor.input');
        $expected = $this->loadFile(__DIR__ . '/extractor.output');

        $extractor = new YamlExtractor();

        self::assertSame(
            array_map(
                fn (string $yaml) => Yaml::parse($yaml),
                explode('<<<<<<<<<<==========>>>>>>>>>>', $expected)
            ),
            $extractor->extract($input),
        );
    }

    public function testWithTemplates(): void
    {
        $input = <<<'YAML'
<nowiki>
templates:
    games: |
        {% for data in games %}
        {{ include('game') }}
        {% endfor %}
    game: |
        {| class="wikitable" style="text-align: center
        |-
        ! colspan="{{ data.headers|length }}" | {{ data.game.name }}
        |-
        ! {{ data.headers|join(' !! ') }}
        {% for columns in data.scores %}
        |-
        | {{ columns|join(' || ') }}
        {% endfor %}
        |}
</nowiki>

YAML;
        $expected = [
            'templates' => [
                'games' => <<<'TPL'
{% for data in games %}
{{ include('game') }}
{% endfor %}

TPL,
                'game' => <<<'TPL'
{| class="wikitable" style="text-align: center
|-
! colspan="{{ data.headers|length }}" | {{ data.game.name }}
|-
! {{ data.headers|join(' !! ') }}
{% for columns in data.scores %}
|-
| {{ columns|join(' || ') }}
{% endfor %}
|}

TPL,
            ],
        ];

        $extractor = new YamlExtractor();

        self::assertSame([$expected], $extractor->extract($input));
    }

    public function testWithInvalidInput(): void
    {
        $input = <<<'INPUT'
<nowiki>
some: bad: o
    syntax:
 example
</nowiki>
INPUT;

        $extractor = new YamlExtractor();

        try {
            print_r($extractor->extract($input));
            self::fail('Call to `extract` should throw an exception.');
        } catch (StgException $exception) {
            self::succeed();
        }
    }
}
