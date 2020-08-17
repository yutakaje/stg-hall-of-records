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
                    'Mushihimesama Futari 1.5',
                    'Cave',
                    [
                        $factory->createScore('ABI', '530,358,660', [
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Normal',
                            'scoredDate' => '2008-01',
                            'source' => 'Arcadia January 2008',
                        ]),
                        $factory->createScore('ISO / Niboshi', '518,902,716', [
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Abnormal',
                            'scoredDate' => '2007',
                            'source' => 'Superplay DVD',
                        ]),
                    ],
                    $factory->createLayout()
                ),
                $factory->createGame(
                    'Ketsui: Kizuna Jigoku Tachi',
                    'Cave',
                    [
                        $factory->createScore('SPS', '507,780,433', [
                            'ship' => 'Type A',
                            'mode' => 'Omote',
                            'scoredDate' => '2014-08',
                            'source' => 'Arcadia August 2014',
                        ]),
                        $factory->createScore('GAN', '569,741,232', [
                            'ship' => 'Type B',
                            'mode' => 'Ura',
                            'scoredDate' => '2016-03',
                            'source' => 'JHA March 2016',
                            'comments' => [
                                '6L remaining',
                                '1st loop 285m',
                            ],
                        ]),
                    ],
                    $factory->createLayout(
                        [
                            $factory->createColumn('Ship', '{{ship}}', [
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn('Loop', '{{mode}}'),
                            $factory->createColumn('Score', '{{score}}'),
                            $factory->createColumn('Player', '{{player}}', [
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn(
                                'Date / Source',
                                '{{scored-date}} / {{source}}'
                            ),
                            $factory->createColumn('Comment', '{{comments}}'),
                        ],
                        [
                            'ship' => 'asc',
                            'mode' => 'asc',
                            'score' => 'desc',
                        ]
                    )
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
                    'Mushihimesama Futari 1.5',
                    'Cave',
                    [
                        $factory->createScore('ABI', '530,358,660', [
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Normal',
                            'scoredDate' => '2008-01',
                            'source' => 'Arcadia January 2008',
                        ]),
                        $factory->createScore('ISO / Niboshi', '518,902,716', [
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Abnormal',
                            'scoredDate' => '2007',
                            'source' => 'Superplay DVD',
                        ]),
                    ],
                    $factory->createLayout()
                ),
                $factory->createGame(
                    'Ketsui: Kizuna Jigoku Tachi',
                    'Cave',
                    [
                        $factory->createScore('SPS', '507,780,433', [
                            'ship' => 'Tiger Schwert',
                            'mode' => 'Omote',
                            'scoredDate' => '2014-08',
                            'source' => 'Arcadia August 2014',
                        ]),
                        $factory->createScore('GAN', '569,741,232', [
                            'ship' => 'Panzer Jäger',
                            'mode' => 'Ura',
                            'scoredDate' => '2016-03',
                            'source' => 'JHA March 2016',
                            'comments' => [
                                '6L remaining',
                                '1st loop 285m',
                            ],
                        ]),
                    ],
                    $factory->createLayout(
                        [
                            $factory->createColumn('Ship', '{{ship}}', [
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn('Loop', '{{mode}}'),
                            $factory->createColumn('Score', '{{score}}'),
                            $factory->createColumn('Player', '{{player}}', [
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn(
                                'Date / Source',
                                '{{scored-date}} / {{source}}'
                            ),
                            $factory->createColumn('Comment', '{{comments}}'),
                        ],
                        [
                            'ship' => 'asc',
                            'mode' => 'asc',
                            'score' => 'desc',
                        ]
                    )
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
                    '虫姫さまふたりVer 1.5',
                    'ケイブ',
                    [
                        $factory->createScore('ABI', '530,358,660', [
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Normal',
                            'scoredDate' => '2008-01',
                            'source' => 'Arcadia January 2008',
                        ]),
                        $factory->createScore('ISO / Niboshi', '518,902,716', [
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Abnormal',
                            'scoredDate' => '2007',
                            'source' => 'Superplay DVD',
                        ]),
                    ],
                    $factory->createLayout()
                ),
                $factory->createGame(
                    'ケツイ ～絆地獄たち～',
                    'ケイブ',
                    [
                        $factory->createScore('SPS', '507,780,433', [
                            'ship' => 'TYPE-A ティーゲルシュベルト',
                            'mode' => '表2週',
                            'scoredDate' => '2014-08',
                            'source' => 'Arcadia August 2014',
                        ]),
                        $factory->createScore('GAN', '569,741,232', [
                            'ship' => 'TYPE-B パンツァーイェーガー',
                            'mode' => '裏2週',
                            'scoredDate' => '2016-03',
                            'source' => 'JHA March 2016',
                            'comments' => [
                                '残6機',
                                '1周 2.85億',
                            ],
                        ]),
                    ],
                    $factory->createLayout(
                        [
                            $factory->createColumn('自機', '{{ship}}', [
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn('2週種', '{{mode}}'),
                            $factory->createColumn('スコア', '{{score}}'),
                            $factory->createColumn('プレイヤー', '{{player}}', [
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn(
                                '年月日 / 情報元',
                                '{{scored-date}} / {{source}}'
                            ),
                            $factory->createColumn('備考', '{{comments}}'),
                        ],
                        [
                            'ship' => 'asc',
                            'mode' => 'asc',
                            'score' => 'desc',
                        ]
                    )
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
                'entries' => [
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
                'entries' => [
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
                            'value' => '{{ship}}',
                            'groupSameValues' => true,
                        ],
                        [
                            'label' => 'Loop',
                            'label-jp' => '2週種',
                            'value' => '{{mode}}',
                        ],
                        [
                            'label' => 'Score',
                            'label-jp' => 'スコア',
                            'value' => '{{score}}',
                        ],
                        [
                            'label' => 'Player',
                            'label-jp' => 'プレイヤー',
                            'value' => '{{player}}',
                            'groupSameValues' => true,
                        ],
                        [
                            'label' => 'Date / Source',
                            'label-jp' => '年月日 / 情報元',
                            'value' => '{{scored-date}} / {{source}}',
                        ],
                        [
                            'label' => 'Comment',
                            'label-jp' => '備考',
                            'value' => '{{comments}}',
                        ],
                    ],
                    'sort' => [
                        'ship' => 'asc',
                        'mode' => 'asc',
                        'score' => 'desc',
                    ],
                ],
            ],
        ];
    }
}
