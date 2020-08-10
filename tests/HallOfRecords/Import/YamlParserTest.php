<?php

declare(strict_types=1);

/*
 * This file is part of the stg/hall-of-records package.
 *
 * (c) YTK <yutakaje@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\HallOfRecords\Import;

use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\Scores;
use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\Games;
use Stg\HallOfRecords\Data\Properties;
use Stg\HallOfRecords\Import\YamlParser;

class YamlParserTest extends \Tests\TestCase
{
    public function testWithNoSections(): void
    {
        $parser = new YamlParser();
        $parser->parse([]);

        self::assertEquals(new Properties(), $parser->globalProperties());
        self::assertEquals(new Games([]), $parser->games());
    }

    public function testWithNoGames(): void
    {
        $global = [
            'name' => 'global',
            'locale' => [
                'property' => 'company',
                'value' => 'Cave',
                'value-jp' => 'ケイブ',
            ],
        ];

        $parser = new YamlParser();
        $parser->parse([
            $global,
        ]);

        self::assertEquals(new Properties($global), $parser->globalProperties());
        self::assertEquals(new Games([]), $parser->games());
    }

    public function testWithGamesAndDefaultLocale(): void
    {
        $global = $this->globalPropertiesInput();
        $games = $this->gamesInput();

        $parser = new YamlParser();
        $parser->parse(array_merge(
            [$global],
            $games
        ));

        self::assertEquals(new Properties($global), $parser->globalProperties());
        self::assertEquals(
            new Games([
                new Game(
                    'Mushihimesama Futari 1.5',
                    'Cave',
                    new Scores([
                        $this->createScore([
                            'player' => 'ABI',
                            'score' => '530,358,660',
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Normal',
                            'scored-date' => '2008-01',
                            'source' => 'Arcadia January 2008',
                        ]),
                        $this->createScore([
                            'player' => 'ISO / Niboshi',
                            'score' => '518,902,716',
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Abnormal',
                            'scored-date' => '2007',
                            'source' => 'Superplay DVD',
                        ]),
                    ]),
                ),
                new Game(
                    'Ketsui: Kizuna Jigoku Tachi',
                    'Cave',
                    new Scores([
                        $this->createScore([
                            'player' => 'SPS',
                            'score' => '507,780,433',
                            'ship' => 'Type A',
                            'mode' => 'Omote',
                            'scored-date' => '2014-08',
                            'source' => 'Arcadia August 2014',
                        ]),
                        $this->createScore([
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
                    ])
                ),
            ]),
            $parser->games()
        );
    }

    public function testWithGamesAndEnLocale(): void
    {
        $global = $this->globalPropertiesInput();
        $games = $this->gamesInput();
        $locale = 'en';

        $parser = new YamlParser($locale);
        $parser->parse(array_merge(
            [$global],
            $games
        ));

        self::assertEquals(
            new Properties($global, $locale),
            $parser->globalProperties()
        );
        self::assertEquals(
            new Games([
                new Game(
                    'Mushihimesama Futari 1.5',
                    'Cave',
                    new Scores([
                        $this->createScore([
                            'player' => 'ABI',
                            'score' => '530,358,660',
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Normal',
                            'scored-date' => '2008-01',
                            'source' => 'Arcadia January 2008',
                        ]),
                        $this->createScore([
                            'player' => 'ISO / Niboshi',
                            'score' => '518,902,716',
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Abnormal',
                            'scored-date' => '2007',
                            'source' => 'Superplay DVD',
                        ]),
                    ]),
                ),
                new Game(
                    'Ketsui: Kizuna Jigoku Tachi',
                    'Cave',
                    new Scores([
                        $this->createScore([
                            'player' => 'SPS',
                            'score' => '507,780,433',
                            'ship' => 'Tiger Schwert',
                            'mode' => 'Omote',
                            'scored-date' => '2014-08',
                            'source' => 'Arcadia August 2014',
                        ]),
                        $this->createScore([
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
                    ])
                ),
            ]),
            $parser->games()
        );
    }

    public function testWithGamesJpLocale(): void
    {
        $global = $this->globalPropertiesInput();
        $games = $this->gamesInput();
        $locale = 'jp';

        $parser = new YamlParser($locale);
        $parser->parse(array_merge(
            [$global],
            $games
        ));

        self::assertEquals(
            new Properties($global, $locale),
            $parser->globalProperties()
        );
        self::assertEquals(
            new Games([
                new Game(
                    '虫姫さまふたりVer 1.5',
                    'ケイブ',
                    new Scores([
                        $this->createScore([
                            'player' => 'ABI',
                            'score' => '530,358,660',
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Normal',
                            'scored-date' => '2008-01',
                            'source' => 'Arcadia January 2008',
                        ]),
                        $this->createScore([
                            'player' => 'ISO / Niboshi',
                            'score' => '518,902,716',
                            'ship' => 'Palm',
                            'mode' => 'Original',
                            'weapon' => 'Abnormal',
                            'scored-date' => '2007',
                            'source' => 'Superplay DVD',
                        ]),
                    ]),
                ),
                new Game(
                    'ケツイ ～絆地獄たち～',
                    'ケイブ',
                    new Scores([
                        $this->createScore([
                            'player' => 'SPS',
                            'score' => '507,780,433',
                            'ship' => 'TYPE-A ティーゲルシュベルト',
                            'mode' => '表2週',
                            'scored-date' => '2014-08',
                            'source' => 'Arcadia August 2014',
                        ]),
                        $this->createScore([
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
                    ])
                ),
            ]),
            $parser->games()
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function createScore(array $properties): Score
    {
        return new Score(
            $properties['player'],
            $properties['score'],
            $properties['ship'],
            $properties['mode'],
            $properties['weapon'] ?? '',
            $properties['scored-date'],
            $properties['source'],
            $properties['comments'] ?? []
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function globalPropertiesInput(): array
    {
        return [
            'name' => 'global',
            'locale' => [
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
                'locale' => [
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
            ],
        ];
    }
}