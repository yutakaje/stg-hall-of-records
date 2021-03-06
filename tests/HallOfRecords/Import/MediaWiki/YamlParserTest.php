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

use Stg\HallOfRecords\Import\MediaWiki\ParsedProperties;
use Stg\HallOfRecords\Import\MediaWiki\YamlParser;

class YamlParserTest extends \Tests\TestCase
{
    public function testWithNoSections(): void
    {
        $parser = new YamlParser();
        $parsedData = $parser->parse([]);

        self::assertEquals(
            new ParsedProperties(),
            $parsedData->get('global-properties')
        );
        self::assertEquals([], $parsedData->get('games'));
    }

    public function testWithNoGames(): void
    {
        $global = $this->globalPropertiesInput();

        $parser = new YamlParser();
        $parsedData = $parser->parse([
            $global,
        ]);

        self::assertEquals(
            new ParsedProperties(array_merge($global, [
                'translations' => array_map(
                    fn (array $entry) => new ParsedProperties($entry),
                    $global['translations']
                ),
                'layout' => new ParsedProperties($global['layout']),
            ])),
            $parsedData->get('global-properties')
        );
        self::assertEquals([], $parsedData->get('games'));
    }

    public function testWithFullData(): void
    {
        $global = $this->globalPropertiesInput();
        $games = $this->gamesInput();

        $parser = new YamlParser();
        $parsedData = $parser->parse(array_merge(
            [$global],
            $games
        ));

        self::assertEquals(
            new ParsedProperties(array_merge($global, [
                'translations' => array_map(
                    fn (array $entry) => new ParsedProperties($entry),
                    $global['translations']
                ),
                'layout' => new ParsedProperties($global['layout']),
            ])),
            $parsedData->get('global-properties')
        );
        self::assertEquals(
            [
                new ParsedProperties([
                    'name' => 'Mushihimesama Futari 1.5',
                    'name-jp' => '虫姫さまふたりVer 1.5',
                    'company' => 'Cave',
                    'scores' => [
                        new ParsedProperties([
                            'player' => 'ABI',
                            'score' => '530,358,660',
                            'score-real' => '530,358,660',
                            'score-sort' => '530358660',
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Normal',
                            'scored-date' => '2008-01',
                            'source' => 'Arcadia January 2008',
                            'links' => [
                                new ParsedProperties([
                                    'url' => 'https://www.example.org/url',
                                    'title' => 'some title',
                                ]),
                            ],
                        ]),
                        new ParsedProperties([
                            'player' => 'gus',
                            'score' => '3,999,999,999',
                            'score-real' => '5,183,657,104',
                            'score-sort' => '5183657104',
                            'ship' => 'Palm',
                            'mode' => 'Ultra',
                            'weapon' => 'Abnormal',
                            'scored-date' => '2020-11',
                        ]),
                    ],
                    'name-sort' => 'Mushihimesama Futari 1.5',
                    'name-sort-jp' => 'むしひめさまふたりVer 1.5',
                ]),
                new ParsedProperties([
                    'name' => 'Ketsui: Kizuna Jigoku Tachi',
                    'name-jp' => 'ケツイ ～絆地獄たち～',
                    'name-sort' => 'Ketsui: Kizuna Jigoku Tachi',
                    'name-sort-jp' => 'けつい ～きずなじごくたち～',
                    'company' => 'Cave',
                    'scores' => [
                        new ParsedProperties([
                            'player' => 'SPS',
                            'score' => '507,780,433',
                            'score-real' => '507,780,433',
                            'score-sort' => '507780433',
                            'ship' => 'Type A',
                            'mode' => 'Omote',
                            'scored-date' => '2014-08',
                            'source' => 'Arcadia August 2014',
                            'comments' => [],
                        ]),
                        new ParsedProperties([
                            'player' => 'GAN',
                            'score' => '569,741,232',
                            'score-real' => '569,741,232',
                            'score-sort' => '569741232',
                            'ship' => 'Type B',
                            'mode' => 'Ura',
                            'scored-date' => '2016-03',
                            'source' => 'JHA March 2016',
                            'comments' => [
                                '6L remaining',
                                '1st loop 285m',
                            ],
                            'comments-jp' => [
                                '残6機',
                                '1周 2.85億',
                            ],
                        ]),
                    ],
                    'links' => [
                        new ParsedProperties([
                            'url' => 'https://example.org/jha/ketsui',
                            'title' => 'JHA Leaderboard',
                            'title-jp' => '日本ハイスコア協会',
                        ]),
                        new ParsedProperties([
                            'url' => 'https://example.org/farm/ketsui',
                            'title' => 'Shmups Forum Hi-Score Topic',
                        ]),
                    ],
                    'translations' => [
                        new ParsedProperties([
                            'property' => 'ship',
                            'value' => 'Type A',
                            'value-en' => 'Tiger Schwert',
                            'value-jp' => 'TYPE-A ティーゲルシュベルト',
                        ]),
                        new ParsedProperties([
                            'property' => 'ship',
                            'value' => 'Type B',
                            'value-en' => 'Panzer Jäger',
                            'value-jp' => 'TYPE-B パンツァーイェーガー',
                        ]),
                        new ParsedProperties([
                            'property' => 'mode',
                            'value' => 'Omote',
                            'value-jp' => '表2週',
                        ]),
                        new ParsedProperties([
                            'property' => 'mode',
                            'value' => 'Ura',
                            'value-jp' => '裏2週',
                        ]),
                    ],
                    'layout' => new ParsedProperties([
                        'column-order' => [
                            'player',
                            'mode',
                            'ship',
                            'score',
                            'scored-date+source',
                            'comments',
                        ],
                        'columns' => [
                            'ship' => new ParsedProperties([
                                'label' => 'Ship',
                                'label-jp' => '自機',
                                'template' => '{{ ship }}',
                                'groupSameValues' => true,
                            ]),
                            'mode' => new ParsedProperties([
                                'label' => 'Loop',
                                'label-jp' => '2週種',
                                'template' => '{{ mode }}',
                            ]),
                            'score' => new ParsedProperties([
                                'label' => 'Score',
                                'label-jp' => 'スコア',
                                'template' => '{{ score }}',
                            ]),
                            'player' => new ParsedProperties([
                                'label' => 'Player',
                                'label-jp' => 'プレイヤー',
                                'template' => '{{ player }}',
                                'groupSameValues' => true,
                            ]),
                            'scored-date+source' => new ParsedProperties([
                                'label' => 'Date / Source',
                                'label-jp' => '年月日 / 情報元',
                                'template' => '{{ scored-date }} / {{ source }}',
                            ]),
                            'comments' => new ParsedProperties([
                                'label' => 'Comment',
                                'label-jp' => '備考',
                                'template' => '{{ comments }}',
                            ]),
                        ],
                        'sort' => [
                            'scores' => [
                                'ship' => 'asc',
                                'mode' => ['Ura', 'Omote'],
                                'score' => 'desc',
                            ],
                        ],
                    ]),
                ]),
                new ParsedProperties([
                    'name' => 'Great Mahou Daisakusen',
                    'company' => 'Raizing / 8ing',
                    'scores' => [],
                    'layout' => new ParsedProperties([
                        'templates' => [
                            'game' => $this->getFixedGameTemplate(),
                        ],
                    ]),
                    'name-sort' => 'Great Mahou Daisakusen',
                ]),
            ],
            $parsedData->get('games')
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function globalPropertiesInput(): array
    {
        return [
            'name' => 'global',
            'description' => 'some description',
            'description-jp' => 'ある説明',
            'translations' => [
                [
                    'property' => 'company',
                    'value' => 'Cave',
                    'value-jp' => 'ケイブ',
                ],
            ],
            'layout' => [
                'templates' => [
                    'games' => <<<'TPL'
{% for game in games %}
{{ include('game') }}
{% endfor %}

TPL,
                    'game' => <<<'TPL'
{| class="wikitable" style="text-align: center
|-
! colspan="{{ game.headers|length }}" | {{ game.properties.name }}
|-
! {{ game.headers|join(' !! ') }}
{% for columns in game.scores %}
|-
| {{ columns|join(' || ') }}
{% endfor %}
|}

TPL,
                ],
            ],
        ];
    }

    /**
     * @return array<string,mixed>[]
     */
    private function gamesInput(): array
    {
        return [
            [
                'name' => 'Mushihimesama Futari 1.5',
                'name-jp' => '虫姫さまふたりVer 1.5',
                'name-kana' => 'むしひめさまふたりVer 1.5',
                'company' => 'Cave',
                'scores' => [
                    [
                        'player' => 'ABI',
                        'score' => '530,358,660',
                        'ship' => 'Palm',
                        'mode' => 'Original',
                        'weapon' => 'Normal',
                        'scored-date' => '2008-01',
                        'source' => 'Arcadia January 2008',
                        'links' => [
                            [
                                'url' => 'https://www.example.org/url',
                                'title' => 'some title',
                            ],
                        ],
                    ],
                    [
                        'player' => 'gus',
                        'score' => '3,999,999,999',
                        'score-real' => '5,183,657,104',
                        'ship' => 'Palm',
                        'mode' => 'Ultra',
                        'weapon' => 'Abnormal',
                        'scored-date' => '2020-11',
                    ],
                ],
            ],
            [
                'name' => 'Ketsui: Kizuna Jigoku Tachi',
                'name-jp' => 'ケツイ ～絆地獄たち～',
                'name-sort-jp' => 'けつい ～きずなじごくたち～',
                'company' => 'Cave',
                'scores' => [
                    [
                        'player' => 'SPS',
                        'score' => '507,780,433',
                        'ship' => 'Type A',
                        'mode' => 'Omote',
                        'scored-date' => '2014-08',
                        'source' => 'Arcadia August 2014',
                        'comments' => [],
                    ],
                    [
                        'player' => 'GAN',
                        'score' => '569,741,232',
                        'ship' => 'Type B',
                        'mode' => 'Ura',
                        'scored-date' => '2016-03',
                        'source' => 'JHA March 2016',
                        'comments' => [
                            '6L remaining',
                            '1st loop 285m',
                        ],
                        'comments-jp' => [
                            '残6機',
                            '1周 2.85億',
                        ],
                    ],
                ],
                'links' => [
                    [
                        'url' => 'https://example.org/jha/ketsui',
                        'title' => 'JHA Leaderboard',
                        'title-jp' => '日本ハイスコア協会',
                    ],
                    [
                        'url' => 'https://example.org/farm/ketsui',
                        'title' => 'Shmups Forum Hi-Score Topic',
                    ],
                ],
                'translations' => [
                    [
                        'property' => 'ship',
                        'value' => 'Type A',
                        'value-en' => 'Tiger Schwert',
                        'value-jp' => 'TYPE-A ティーゲルシュベルト',
                    ],
                    [
                        'property' => 'ship',
                        'value' => 'Type B',
                        'value-en' => 'Panzer Jäger',
                        'value-jp' => 'TYPE-B パンツァーイェーガー',
                    ],
                    [
                        'property' => 'mode',
                        'value' => 'Omote',
                        'value-jp' => '表2週',
                    ],
                    [
                        'property' => 'mode',
                        'value' => 'Ura',
                        'value-jp' => '裏2週',
                    ],
                ],
                'layout' => [
                    'column-order' => [
                        'player',
                        'mode',
                        'ship',
                        'score',
                        'scored-date+source',
                        'comments',
                    ],
                    'columns' => [
                        'ship' => [
                            'label' => 'Ship',
                            'label-jp' => '自機',
                            'template' => '{{ ship }}',
                            'groupSameValues' => true,
                        ],
                        'mode' => [
                            'label' => 'Loop',
                            'label-jp' => '2週種',
                            'template' => '{{ mode }}',
                        ],
                        'score' => [
                            'label' => 'Score',
                            'label-jp' => 'スコア',
                            'template' => '{{ score }}',
                        ],
                        'player' => [
                            'label' => 'Player',
                            'label-jp' => 'プレイヤー',
                            'template' => '{{ player }}',
                            'groupSameValues' => true,
                        ],
                        'scored-date+source' => [
                            'label' => 'Date / Source',
                            'label-jp' => '年月日 / 情報元',
                            'template' => '{{ scored-date }} / {{ source }}',
                        ],
                        'comments' => [
                            'label' => 'Comment',
                            'label-jp' => '備考',
                            'template' => '{{ comments }}',
                        ],
                    ],
                    'sort' => [
                        'scores' => [
                            'ship' => 'asc',
                            'mode' => ['Ura', 'Omote'],
                            'score' => 'desc',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Great Mahou Daisakusen',
                'company' => 'Raizing / 8ing',
                'scores' => [],
                'layout' => [
                    'templates' => [
                        'game' => $this->getFixedGameTemplate(),
                    ],
                ],
            ]
        ];
    }

    private function getFixedGameTemplate(): string
    {
        return <<<'TPL'
{| class="wikitable" style="text-align: center"
|-
! colspan="6" | [[Great Mahou Daisakusen]]
|-
! Ship !! Score !! Player !! Date / Source !! Comment !! Replay
|-
| rowspan="2" | Birthday
| 83,743,680 || rowspan="2" | Miku || August 2nd, 2020 / [https://example.org Twitter] || 107 items ||
|-
| 66,693,110 || JHA November 2019 || 107 items ||
|-
| rowspan="2" | Chitta
| 93,664,750 || rowspan="2" | SOF-WTN
| August 8th, 2020 / [https://twitter.com/sof_wtn/status/1292047346562289664 Twitter] || 108 items ||
|-
| 83,195,810 || JHA June 2020 || ||
|-
| rowspan="2" | Gain
| 80,528,610 || Boredom || July 1st, 2020 / [https:// Twitter] || 108 items || [https:// Youtube]
|-
| 31,653,130 || HTL-蕨ガイン見参 || JHA June 2020 || ||
|-
|}

Note: Scoreboard closed after the achievement of the counterstop at 99,999,999.

* [https://example.org/some_link_id JHA Leaderboard]
* [https://example.org/some_other_link Shmups Forum Hi-Score Topic]

TPL;
    }
}
