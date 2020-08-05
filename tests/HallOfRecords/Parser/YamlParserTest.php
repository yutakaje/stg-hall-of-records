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

namespace Tests\HallOfRecords\Parser;

use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\Scores;
use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\Games;
use Stg\HallOfRecords\Data\Properties;
use Stg\HallOfRecords\Parser\YamlParser;

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
                        new Score(
                            'ABI',
                            '530,358,660',
                            'Original',
                            'Palm',
                            'Normal',
                            '',
                            '2008-01',
                            'Arcadia January 2008',
                            ''
                        ),
                        new Score(
                            'ISO / Niboshi',
                            '518,902,716',
                            'Original',
                            'Palm',
                            'Abnormal',
                            '',
                            '2007',
                            'Superplay DVD',
                            ''
                        ),
                    ]),
                ),
                new Game(
                    'Ketsui: Kizuna Jigoku Tachi',
                    'Cave',
                    new Scores([
                        new Score(
                            'SPS',
                            '507,780,433',
                            '',
                            'Type A',
                            '',
                            'Omote',
                            '2014-08',
                            'Arcadia August 2014',
                            ''
                        ),
                        new Score(
                            'SPS',
                            '583,614,753',
                            '',
                            'Type A',
                            '',
                            'Ura',
                            '2014-05-27',
                            'Arcadia September 2014',
                            '6L 0B remaining; 1st loop 285m'
                        ),
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
                        new Score(
                            'ABI',
                            '530,358,660',
                            'Original',
                            'Palm',
                            'Normal',
                            '',
                            '2008-01',
                            'Arcadia January 2008',
                            ''
                        ),
                        new Score(
                            'ISO / Niboshi',
                            '518,902,716',
                            'Original',
                            'Palm',
                            'Abnormal',
                            '',
                            '2007',
                            'Superplay DVD',
                            ''
                        ),
                    ]),
                ),
                new Game(
                    'Ketsui: Kizuna Jigoku Tachi',
                    'Cave',
                    new Scores([
                        new Score(
                            'SPS',
                            '507,780,433',
                            '',
                            'Type A',
                            '',
                            'Omote',
                            '2014-08',
                            'Arcadia August 2014',
                            ''
                        ),
                        new Score(
                            'SPS',
                            '583,614,753',
                            '',
                            'Type A',
                            '',
                            'Ura',
                            '2014-05-27',
                            'Arcadia September 2014',
                            '6L 0B remaining; 1st loop 285m'
                        ),
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
                        new Score(
                            'ABI',
                            '530,358,660',
                            'Original',
                            'Palm',
                            'Normal',
                            '',
                            '2008-01',
                            'Arcadia January 2008',
                            ''
                        ),
                        new Score(
                            'ISO / Niboshi',
                            '518,902,716',
                            'Original',
                            'Palm',
                            'Abnormal',
                            '',
                            '2007',
                            'Superplay DVD',
                            ''
                        ),
                    ]),
                ),
                new Game(
                    'ケツイ ～絆地獄たち～',
                    'ケイブ',
                    new Scores([
                        new Score(
                            'SPS',
                            '507,780,433',
                            '',
                            'Type A',
                            '',
                            'Omote',
                            '2014-08',
                            'Arcadia August 2014',
                            ''
                        ),
                        new Score(
                            'SPS',
                            '583,614,753',
                            '',
                            'Type A',
                            '',
                            'Ura',
                            '2014-05-27',
                            'Arcadia September 2014',
                            '6L 0B remaining; 1st loop 285m'
                        ),
                    ])
                ),
            ]),
            $parser->games()
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
                        'mode' => 'Original',
                        'character' => 'Palm',
                        'weapon' => 'Normal',
                        'score' => '530,358,660',
                        'player' => 'ABI',
                        'date' => '2008-01',
                        'source' => 'Arcadia January 2008',
                    ],
                    [
                        'mode' => 'Original',
                        'character' => 'Palm',
                        'weapon' => 'Abnormal',
                        'score' => '518,902,716',
                        'player' => 'ISO / Niboshi',
                        'date' => '2007',
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
                        'character' => 'Type A',
                        'stage' => 'Omote',
                        'score' => '507,780,433',
                        'player' => 'SPS',
                        'date' => '2014-08',
                        'source' => 'Arcadia August 2014',
                        'comment' => '',
                    ],
                    [
                        'character' => 'Type A',
                        'stage' => 'Ura',
                        'score' => '583,614,753',
                        'player' => 'SPS',
                        'date' => '2014-05-27',
                        'source' => 'Arcadia September 2014',
                        'comment' => '6L 0B remaining; 1st loop 285m',
                    ],
                ],
            ],
        ];
    }
}
