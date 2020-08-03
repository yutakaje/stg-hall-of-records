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

use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\Games;
use Stg\HallOfRecords\Data\GlobalProperties;
use Stg\HallOfRecords\Parser\Parser;

class ParserTest extends \Tests\TestCase
{
    public function testWithNoSections(): void
    {
        $parser = new Parser([]);

        self::assertEquals(new GlobalProperties(), $parser->parseGlobalProperties());
        self::assertEquals(new Games([]), $parser->parseGames());
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

        $parser = new Parser([
            $global,
        ]);

        self::assertEquals(
            new GlobalProperties($global),
            $parser->parseGlobalProperties()
        );
        self::assertEquals(new Games([]), $parser->parseGames());
    }

    public function testWithGames(): void
    {
        $global = $this->globalPropertiesInput();
        $games = $this->gamesInput();

        $parser = new Parser(array_merge(
            [$global],
            $games
        ));

        self::assertEquals(
            new GlobalProperties($global),
            $parser->parseGlobalProperties()
        );
        self::assertEquals(
            new Games(array_map(
                fn (array $game) => new Game($game),
                $games
            )),
            $parser->parseGames()
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
                'property' => 'company',
                'value' => 'Cave',
                'value-jp' => 'ケイブ',
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
