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

use Stg\HallOfRecords\Import\ParsedDataFactory;
use Stg\HallOfRecords\Import\YamlParser;

class YamlParserTest extends \Tests\TestCase
{
    public function testWithNoSections(): void
    {
        $parser = new YamlParser();
        $parsedData = $parser->parse([]);

        $factory = new ParsedDataFactory();

        self::assertEquals(
            $factory->createGlobalProperties(),
            $parsedData->globalProperties()
        );
        self::assertEquals([], $parsedData->games());
    }

    public function testWithNoGames(): void
    {
        $global = $this->globalPropertiesInput();

        $parser = new YamlParser();
        $parsedData = $parser->parse([
            $global,
        ]);

        $factory = new ParsedDataFactory();

        self::assertEquals(
            $factory->createGlobalProperties([
                'description' => 'some description',
            ]),
            $parsedData->globalProperties()
        );
        self::assertEquals([], $parsedData->games());
    }

    public function testWithGlobalTemplates(): void
    {
        $global = $this->globalPropertiesInput();
        $global['templates'] = [
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
        ];

        $parser = new YamlParser();
        $parsedData = $parser->parse([
            $global,
        ]);

        $factory = new ParsedDataFactory();

        self::assertEquals(
            $factory->createGlobalProperties([
                'description' => 'some description',
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
            ]),
            $parsedData->globalProperties()
        );
    }

    public function testWithGamesAndDefaultLocale(): void
    {
        $global = $this->globalPropertiesInput();
        $games = $this->gamesInput();

        $parser = new YamlParser();
        $parsedData = $parser->parse(array_merge(
            [$global],
            $games
        ));

        $factory = new ParsedDataFactory();

        self::assertEquals(
            $factory->createGlobalProperties([
                'description' => 'some description',
            ]),
            $parsedData->globalProperties()
        );
        self::assertEquals(
            [
                $factory->createGame(
                    [
                        'name' => 'Mushihimesama Futari 1.5',
                        'company' => 'Cave',
                    ],
                    [
                        $factory->createScore([
                            'player' => 'ABI',
                            'score' => '530,358,660',
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Normal',
                            'scored-date' => '2008-01',
                            'source' => 'Arcadia January 2008',
                        ]),
                        $factory->createScore([
                            'player' => 'ISO / Niboshi',
                            'score' => '518,902,716',
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Abnormal',
                            'scored-date' => '2007',
                            'source' => 'Superplay DVD',
                        ]),
                    ],
                    $factory->createLayout()
                ),
                $factory->createGame(
                    [
                        'name' => 'Ketsui: Kizuna Jigoku Tachi',
                        'company' => 'Cave',
                    ],
                    [
                        $factory->createScore([
                            'player' => 'SPS',
                            'score' => '507,780,433',
                            'ship' => 'Type A',
                            'mode' => 'Omote',
                            'scored-date' => '2014-08',
                            'source' => 'Arcadia August 2014',
                            'comments' => [],
                        ]),
                        $factory->createScore([
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
                        ]),
                    ],
                    $factory->createLayout([
                        'columns' => [
                            $factory->createColumn([
                                'label' => 'Ship',
                                'template' => '{{ ship }}',
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn([
                                'label' => 'Loop',
                                'template' => '{{ mode }}',
                            ]),
                            $factory->createColumn([
                                'label' => 'Score',
                                'template' => '{{ score }}',
                            ]),
                            $factory->createColumn([
                                'label' => 'Player',
                                'template' => '{{ player }}',
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn([
                                'label' => 'Date / Source',
                                'template' => '{{ scored-date }} / {{ source }}',
                            ]),
                            $factory->createColumn([
                                'label' => 'Comment',
                                'template' => '{{ comments }}',
                            ]),
                        ],
                        'sort' => [
                            'ship' => 'asc',
                            'mode' => 'asc',
                            'score' => 'desc',
                        ],
                    ])
                ),
                $factory->createGame(
                    [
                        'name' => 'Great Mahou Daisakusen',
                        'company' => 'Raizing / 8ing',
                    ],
                    [],
                    $factory->createLayout([
                        'templates' => [
                            'game' => $this->getFixedGameTemplate(),
                        ],
                    ])
                ),
            ],
            $parsedData->games()
        );
    }

    public function testWithGamesAndEnLocale(): void
    {
        $global = $this->globalPropertiesInput();
        $games = $this->gamesInput();
        $locale = 'en';

        $parser = new YamlParser();
        $parsedData = $parser->parse(
            array_merge(
                [$global],
                $games
            ),
            $locale
        );

        $factory = new ParsedDataFactory();

        self::assertEquals(
            $factory->createGlobalProperties([
                'description' => 'some description',
            ]),
            $parsedData->globalProperties()
        );
        self::assertEquals(
            [
                $factory->createGame(
                    [
                        'name' => 'Mushihimesama Futari 1.5',
                        'company' => 'Cave',
                    ],
                    [
                        $factory->createScore([
                            'player' => 'ABI',
                            'score' => '530,358,660',
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Normal',
                            'scored-date' => '2008-01',
                            'source' => 'Arcadia January 2008',
                        ]),
                        $factory->createScore([
                            'player' => 'ISO / Niboshi',
                            'score' => '518,902,716',
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Abnormal',
                            'scored-date' => '2007',
                            'source' => 'Superplay DVD',
                        ]),
                    ],
                    $factory->createLayout()
                ),
                $factory->createGame(
                    [
                        'name' => 'Ketsui: Kizuna Jigoku Tachi',
                        'company' => 'Cave',
                    ],
                    [
                        $factory->createScore([
                            'player' => 'SPS',
                            'score' => '507,780,433',
                            'ship' => 'Tiger Schwert',
                            'mode' => 'Omote',
                            'scored-date' => '2014-08',
                            'source' => 'Arcadia August 2014',
                            'comments' => [],
                        ]),
                        $factory->createScore([
                            'player' => 'GAN',
                            'score' => '569,741,232',
                            'ship' => 'Panzer Jäger',
                            'mode' => 'Ura',
                            'scored-date' => '2016-03',
                            'source' => 'JHA March 2016',
                            'comments' => [
                                '6L remaining',
                                '1st loop 285m',
                            ],
                        ]),
                    ],
                    $factory->createLayout([
                        'columns' => [
                            $factory->createColumn([
                                'label' => 'Ship',
                                'template' => '{{ ship }}',
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn([
                                'label' => 'Loop',
                                'template' => '{{ mode }}',
                            ]),
                            $factory->createColumn([
                                'label' => 'Score',
                                'template' => '{{ score }}',
                            ]),
                            $factory->createColumn([
                                'label' => 'Player',
                                'template' => '{{ player }}',
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn([
                                'label' => 'Date / Source',
                                'template' => '{{ scored-date }} / {{ source }}',
                            ]),
                            $factory->createColumn([
                                'label' => 'Comment',
                                'template' => '{{ comments }}',
                            ]),
                        ],
                        'sort' => [
                            'ship' => 'asc',
                            'mode' => 'asc',
                            'score' => 'desc',
                        ],
                    ])
                ),
                $factory->createGame(
                    [
                        'name' => 'Great Mahou Daisakusen',
                        'company' => 'Raizing / 8ing',
                    ],
                    [],
                    $factory->createLayout([
                        'templates' => [
                            'game' => $this->getFixedGameTemplate(),
                        ],
                    ])
                ),
            ],
            $parsedData->games()
        );
    }

    public function testWithGamesJpLocale(): void
    {
        $global = $this->globalPropertiesInput();
        $games = $this->gamesInput();
        $locale = 'jp';

        $parser = new YamlParser();
        $parsedData = $parser->parse(
            array_merge(
                [$global],
                $games
            ),
            $locale
        );

        $factory = new ParsedDataFactory();

        self::assertEquals(
            $factory->createGlobalProperties([
                'description' => 'ある説明',
            ]),
            $parsedData->globalProperties()
        );
        self::assertEquals(
            [
                $factory->createGame(
                    [
                        'name' => '虫姫さまふたりVer 1.5',
                        'company' => 'ケイブ',
                    ],
                    [
                        $factory->createScore([
                            'player' => 'ABI',
                            'score' => '530,358,660',
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Normal',
                            'scored-date' => '2008-01',
                            'source' => 'Arcadia January 2008',
                        ]),
                        $factory->createScore([
                            'player' => 'ISO / Niboshi',
                            'score' => '518,902,716',
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Abnormal',
                            'scored-date' => '2007',
                            'source' => 'Superplay DVD',
                        ]),
                    ],
                    $factory->createLayout()
                ),
                $factory->createGame(
                    [
                        'name' => 'ケツイ ～絆地獄たち～',
                        'company' => 'ケイブ',
                    ],
                    [
                        $factory->createScore([
                            'player' => 'SPS',
                            'score' => '507,780,433',
                            'ship' => 'TYPE-A ティーゲルシュベルト',
                            'mode' => '表2週',
                            'scored-date' => '2014-08',
                            'source' => 'Arcadia August 2014',
                            'comments' => [],
                        ]),
                        $factory->createScore([
                            'player' => 'GAN',
                            'score' => '569,741,232',
                            'ship' => 'TYPE-B パンツァーイェーガー',
                            'mode' => '裏2週',
                            'scored-date' => '2016-03',
                            'source' => 'JHA March 2016',
                            'comments' => [
                                '残6機',
                                '1周 2.85億',
                            ],
                        ]),
                    ],
                    $factory->createLayout([
                        'columns' => [
                            $factory->createColumn([
                                'label' => '自機',
                                'template' => '{{ ship }}',
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn([
                                'label' => '2週種',
                                'template' => '{{ mode }}',
                            ]),
                            $factory->createColumn([
                                'label' => 'スコア',
                                'template' => '{{ score }}',
                            ]),
                            $factory->createColumn([
                                'label' => 'プレイヤー',
                                'template' => '{{ player }}',
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn([
                                'label' => '年月日 / 情報元',
                                'template' => '{{ scored-date }} / {{ source }}',
                            ]),
                            $factory->createColumn([
                                'label' => '備考',
                                'template' => '{{ comments }}',
                            ]),
                        ],
                        'sort' => [
                            'ship' => 'asc',
                            'mode' => 'asc',
                            'score' => 'desc',
                        ],
                    ])
                ),
                $factory->createGame(
                    [
                        'name' => 'Great Mahou Daisakusen',
                        'company' => 'Raizing / 8ing',
                    ],
                    [],
                    $factory->createLayout([
                        'templates' => [
                            'game' => $this->getFixedGameTemplate(),
                        ],
                    ])
                ),
            ],
            $parsedData->games()
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
