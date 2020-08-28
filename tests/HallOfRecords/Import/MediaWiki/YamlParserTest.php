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
            new ParsedProperties($global),
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
            new ParsedProperties($global),
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
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Normal',
                            'scored-date' => '2008-01',
                            'source' => 'Arcadia January 2008',
                        ]),
                        new ParsedProperties([
                            'player' => 'ISO / Niboshi',
                            'score' => '518,902,716',
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Abnormal',
                            'scored-date' => '2007',
                            'source' => 'Superplay DVD',
                        ]),
                    ],
                    'layout' => new ParsedProperties([
                        'columns' => [],
                    ]),
                ]),
                new ParsedProperties([
                    'name' => 'Ketsui: Kizuna Jigoku Tachi',
                    'name-jp' => 'ケツイ ～絆地獄たち～',
                    'company' => 'Cave',
                    'scores' => [
                        new ParsedProperties([
                            'player' => 'SPS',
                            'score' => '507,780,433',
                            'ship' => 'Type A',
                            'mode' => 'Omote',
                            'scored-date' => '2014-08',
                            'source' => 'Arcadia August 2014',
                            'comments' => [],
                        ]),
                        new ParsedProperties([
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
                        ]),
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
                    'layout' => new ParsedProperties([
                        'columns' => [
                            new ParsedProperties([
                                'label' => 'Ship',
                                'label-jp' => '自機',
                                'template' => '{{ ship }}',
                                'groupSameValues' => true,
                            ]),
                            new ParsedProperties([
                                'label' => 'Loop',
                                'label-jp' => '2週種',
                                'template' => '{{ mode }}',
                            ]),
                            new ParsedProperties([
                                'label' => 'Score',
                                'label-jp' => 'スコア',
                                'template' => '{{ score }}',
                            ]),
                            new ParsedProperties([
                                'label' => 'Player',
                                'label-jp' => 'プレイヤー',
                                'template' => '{{ player }}',
                                'groupSameValues' => true,
                            ]),
                            new ParsedProperties([
                                'label' => 'Date / Source',
                                'label-jp' => '年月日 / 情報元',
                                'template' => '{{ scored-date }} / {{ source }}',
                            ]),
                            new ParsedProperties([
                                'label' => 'Comment',
                                'label-jp' => '備考',
                                'template' => '{{ comments }}',
                            ]),
                        ],
                        'sort' => [
                            'ship' => 'asc',
                            'mode' => 'asc',
                            'score' => 'desc',
                        ],
                    ]),
                ]),
                new ParsedProperties([
                    'name' => 'Great Mahou Daisakusen',
                    'company' => 'Raizing / 8ing',
                    'scores' => [],
                    'layout' => new ParsedProperties([
                        'columns' => [],
                        'templates' => [
                            'game' => $this->getFixedGameTemplate(),
                        ],
                    ]),
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
                    ],
                    [
                        'player' => 'ISO / Niboshi',
                        'score' => '518,902,716',
                        'ship' => 'Palm',
                        'mode' => 'Original',
                        'weapon' => 'Abnormal',
                        'scored-date' => '2007',
                        'source' => 'Superplay DVD',
                    ],
                ],
            ],
            [
                'name' => 'Ketsui: Kizuna Jigoku Tachi',
                'name-jp' => 'ケツイ ～絆地獄たち～',
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
                    'columns' => [
                        [
                            'label' => 'Ship',
                            'label-jp' => '自機',
                            'template' => '{{ ship }}',
                            'groupSameValues' => true,
                        ],
                        [
                            'label' => 'Loop',
                            'label-jp' => '2週種',
                            'template' => '{{ mode }}',
                        ],
                        [
                            'label' => 'Score',
                            'label-jp' => 'スコア',
                            'template' => '{{ score }}',
                        ],
                        [
                            'label' => 'Player',
                            'label-jp' => 'プレイヤー',
                            'template' => '{{ player }}',
                            'groupSameValues' => true,
                        ],
                        [
                            'label' => 'Date / Source',
                            'label-jp' => '年月日 / 情報元',
                            'template' => '{{ scored-date }} / {{ source }}',
                        ],
                        [
                            'label' => 'Comment',
                            'label-jp' => '備考',
                            'template' => '{{ comments }}',
                        ],
                    ],
                    'sort' => [
                        'ship' => 'asc',
                        'mode' => 'asc',
                        'score' => 'desc',
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
