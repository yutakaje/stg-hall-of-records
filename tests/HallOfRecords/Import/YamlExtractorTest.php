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

namespace Tests\HallOfRecords\Import;

use Stg\HallOfRecords\Import\YamlExtractor;
use Symfony\Component\Yaml\Yaml;

class YamlExtractorTest extends \Tests\TestCase
{
    public function testWithValidInput(): void
    {
        $input = $this->loadFile(__DIR__ . '/wiki-input');
        $expected = $this->loadFile(__DIR__ . '/yaml-output');

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
}
