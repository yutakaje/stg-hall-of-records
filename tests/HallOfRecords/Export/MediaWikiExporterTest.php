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

namespace Tests\HallOfRecords\Export;

use Stg\HallOfRecords\Data\Game\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Game\Games;
use Stg\HallOfRecords\Data\Score\ScoreRepositoryInterface;
use Stg\HallOfRecords\Data\Score\Scores;
use Stg\HallOfRecords\Data\Setting\SettingRepositoryInterface;
use Stg\HallOfRecords\Data\Setting\Settings;
use Stg\HallOfRecords\Export\MediaWikiExporter;

class MediaWikiExporterTest extends \Tests\TestCase
{
    public function testExport(): void
    {
        $exporter = new MediaWikiExporter(
            $this->createSettingRepository(),
            $this->createGameRepository(),
            $this->createScoreRepository()
        );

        self::assertSame(
            $this->loadFile(__DIR__ . '/exporter.output'),
            $exporter->export()
        );
    }

    private function createSettingRepository(): SettingRepositoryInterface
    {
        $gameIds = $this->gameIds();

        $settings = $this->createMock(SettingRepositoryInterface::class);
        $settings->method('filterGlobal')
            ->willReturn(new Settings([
                $this->createGlobalSetting([
                    'name' => 'description',
                    'value' => <<<'TEXT'
some description
spanning

multiple lines
TEXT,
                ]),
                $this->createGlobalSetting([
                    'name' => 'layout',
                    'value' => [
                        'group' => [
                            'scores' => [
                                'ship',
                                'mode',
                                'weapon',
                                'version',
                            ],
                        ],
                        'column-order' => [
                            'player',
                            'ship',
                            'mode',
                            'weapon',
                            'score',
                            'scored-date+source',
                            'comments',
                        ],
                        'columns' => [
                            'player' => [
                                'label' => 'Player',
                                'template' => '{{ player }}',
                            ],
                            'score' => [
                                'label' => 'Score',
                                'template' => '{{ score }}',
                            ],
                            'ship' => [
                                'label' => 'Ship',
                                'template' => '{{ ship }}',
                            ],
                            'mode' => [
                                'label' => 'Mode',
                                'template' => '{{ mode }}',
                            ],
                            'weapon' => [
                                'label' => 'Weapon',
                                'template' => '{{ weapon }}',
                            ],
                            'scored-date+source' => [
                                'label' => 'Date / Source',
                                'template' => '{{ scored-date }} / {{ source }}',
                            ],
                            'comments' => [
                                'label' => 'Comments',
                                'template' => "{{ comments|join('; ') }}",
                            ],
                        ],
                        'templates' => $this->templates(),
                    ],
                ]),
            ]));
        $settings->method('filterByGame')
            ->will(self::returnCallback(
                function (int $gameId) use ($gameIds): Settings {
                    switch ($gameId) {
                        case $gameIds[0]:
                            return new Settings([
                                $this->createGameSetting([
                                    'gameId' => $gameIds[0],
                                    'name' => 'layout',
                                    'value' => [
                                        'column-order' => [
                                            'mode',
                                            'ship',
                                            'weapon',
                                            'score',
                                            'player',
                                            'scored-date+source',
                                            'comments',
                                        ],
                                        'columns' => [
                                            'ship' => [
                                                'label' => 'Character',
                                            ],
                                            'weapon' => [
                                                'label' => 'Style',
                                            ],
                                        ],
                                        'sort' => [
                                            'scores' => [
                                                'mode' => [
                                                    'Original',
                                                    'Maniac',
                                                    'Ultra',
                                                ],
                                                'ship' => [
                                                    'Reco',
                                                    'Palm',
                                                ],
                                                'weapon' => [
                                                    'Normal',
                                                    'Abnormal',
                                                ],
                                                'score' => 'desc',
                                            ],
                                        ],
                                    ],
                                ]),
                            ]);
                        case $gameIds[1]:
                            return new Settings([
                                $this->createGameSetting([
                                    'gameId' => $gameIds[1],
                                    'name' => 'layout',
                                    'value' => [
                                        'columns' => [
                                            'mode' => [
                                                'label' => 'Loop',
                                            ],
                                        ],
                                        'sort' => [
                                            'scores' => [
                                                'ship' => 'asc',
                                                'mode' => 'asc',
                                                'score' => 'desc',
                                            ],
                                        ],
                                    ],
                                ]),
                            ]);
                        case $gameIds[2]:
                            return new Settings([
                                $this->createGameSetting([
                                    'gameId' => $gameIds[2],
                                    'name' => 'layout',
                                    'value' => [
                                        'templates' => [
                                            'game' => $this->fixedGameTemplate(),
                                        ],
                                    ],
                                ]),
                            ]);
                        default:
                            return new Settings([]);
                    }
                }
            ));

        return $settings;
    }

    private function createGameRepository(): GameRepositoryInterface
    {
        $gameIds = $this->gameIds();

        $games = $this->createMock(GameRepositoryInterface::class);
        $games->method('all')
            ->willReturn(new Games([
                $this->createGame([
                    'id' => $gameIds[0],
                    'name' => 'Mushihimesama Futari 1.5',
                    'company' => 'Cave',
                ]),
                $this->createGame([
                    'id' => $gameIds[1],
                    'name' => 'Ketsui: Kizuna Jigoku Tachi',
                    'company' => 'Cave',
                ]),
                $this->createGame([
                    'id' => $gameIds[2],
                    'name' => 'Great Mahou Daisakusen',
                    'company' => 'Raizing / 8ing',
                ]),
            ]));

        return $games;
    }

    private function createScoreRepository(): ScoreRepositoryInterface
    {
        $gameIds = $this->gameIds();
        $scoreIds = $this->scoreIds();

        $scores = $this->createMock(ScoreRepositoryInterface::class);
        $scores->method('filterByGame')
            ->will(self::returnCallback(
                function (int $gameId) use ($gameIds, $scoreIds): Scores {
                    switch ($gameId) {
                        case $gameIds[0]:
                            return new Scores([
                                $this->createScore([
                                    'id' => $scoreIds[0],
                                    'gameId' => $gameIds[0],
                                    'player' => 'ABI',
                                    'score' => '530,358,660',
                                    'ship' => 'Palm',
                                    'mode' => 'Original',
                                    'weapon' => 'Normal',
                                    'scored-date' => '2008-01',
                                    'source' => 'Arcadia January 2008',
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[1],
                                    'gameId' => $gameIds[0],
                                    'player' => 'ISO / Niboshi',
                                    'score' => '518,902,716',
                                    'ship' => 'Palm',
                                    'mode' => 'Original',
                                    'weapon' => 'Abnormal',
                                    'scored-date' => '2007',
                                    'source' => 'Superplay DVD',
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[2],
                                    'gameId' => $gameIds[0],
                                    'player' => 'ABI',
                                    'score' => '550,705,999',
                                    'ship' => 'Reco',
                                    'mode' => 'Original',
                                    'weapon' => 'Normal',
                                    'scored-date' => '2010-02',
                                    'source' => 'Blog',
                                    'comments' => [
                                        '5L 0B remaining',
                                        'After stage 4: 273.7m',
                                    ],
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[3],
                                    'gameId' => $gameIds[0],
                                    'player' => 'ISO / Niboshi',
                                    'score' => '538,378,364',
                                    'ship' => 'Reco',
                                    'mode' => 'Original',
                                    'weapon' => 'Normal',
                                    'scored-date' => '2007-10',
                                    'source' => 'Arcadia October 2007',
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[4],
                                    'gameId' => $gameIds[0],
                                    'player' => 'yasu0219',
                                    'score' => '454,386,226',
                                    'ship' => 'Reco',
                                    'mode' => 'Original',
                                    'weapon' => 'Abnormal',
                                    'scored-date' => '2009-12-12',
                                    'source' => 'Xbox rankings',
                                    'comments' => [
                                        'Highest score Xbox360',
                                    ],
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[5],
                                    'gameId' => $gameIds[0],
                                    'player' => 'KTL-NAL',
                                    'score' => '981,872,827',
                                    'ship' => 'Palm',
                                    'mode' => 'Maniac',
                                    'weapon' => 'Abnormal',
                                    'scored-date' => '2007-09',
                                    'source' => 'Superplay DVD',
                                    'comments' => [
                                        '5L 2B remaining',
                                        'After stage 4: 693.8m',
                                    ],
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[6],
                                    'gameId' => $gameIds[0],
                                    'player' => 'KTL-NAL',
                                    'score' => '973,020,065',
                                    'ship' => 'Palm',
                                    'mode' => 'Maniac',
                                    'weapon' => 'Abnormal',
                                    'scored-date' => '2007-11',
                                    'source' => 'Arcadia November 2007',
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[7],
                                    'gameId' => $gameIds[0],
                                    'player' => 'Clover-TAC',
                                    'score' => '1,047,258,714',
                                    'ship' => 'Reco',
                                    'mode' => 'Maniac',
                                    'weapon' => 'Normal',
                                    'scored-date' => '2015-03',
                                    'source' => 'Arcadia March 2015',
                                    'comments' => [
                                        '5L 2B remaining',
                                        'After stage 4: 745.1m',
                                    ],
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[8],
                                    'gameId' => $gameIds[0],
                                    'player' => 'rescue_STG',
                                    'score' => '2,956,728,306',
                                    'ship' => 'Palm',
                                    'mode' => 'Ultra',
                                    'weapon' => 'Normal',
                                    'scored-date' => '2017-04-08',
                                    'source' => 'Xbox rankings',
                                    'comments' => [
                                        'Highest score Xbox360',
                                    ],
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[9],
                                    'gameId' => $gameIds[0],
                                    'player' => 'Dame K.K',
                                    'score' => '3,999,999,999',
                                    'ship' => 'Palm',
                                    'mode' => 'Ultra',
                                    'weapon' => 'Abnormal',
                                    'scored-date' => '2008-03',
                                    'source' => 'Arcadia March 2008',
                                    'comments' => [
                                        '1L 0B remaining',
                                        'Highest score Arcade',
                                    ],
                                    'attributes' => [
                                        'is-current-record' => true,
                                    ],
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[10],
                                    'gameId' => $gameIds[0],
                                    'player' => 'KGM',
                                    'score' => '3,999,999,999 [4,263,416,356]',
                                    'ship' => 'Palm',
                                    'mode' => 'Ultra',
                                    'weapon' => 'Abnormal',
                                    'scored-date' => '2013-07-24',
                                    'source' => 'Xbox rankings',
                                    'comments' => [
                                        'Highest score Xbox360',
                                    ],
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[11],
                                    'gameId' => $gameIds[0],
                                    'player' => 'fufufu',
                                    'score' => '3,999,999,999',
                                    'ship' => 'Reco',
                                    'mode' => 'Ultra',
                                    'weapon' => 'Normal',
                                    'scored-date' => '2009-05-27',
                                    'source' => 'Arcadia August 2009',
                                    'comments' => [
                                        '0L 0B remaining',
                                        'After stage 4: 2.205b',
                                    ],
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[12],
                                    'gameId' => $gameIds[0],
                                    'player' => 'lstze',
                                    'score' => '3,266,405,598',
                                    'ship' => 'Reco',
                                    'mode' => 'Ultra',
                                    'weapon' => 'Abnormal',
                                    'scored-date' => '2014?',
                                ]),
                            ]);
                        case $gameIds[1]:
                            return new Scores([
                                $this->createScore([
                                    'id' => $scoreIds[13],
                                    'gameId' => $gameIds[1],
                                    'player' => 'SPS',
                                    'score' => '507,780,433',
                                    'ship' => 'Type A',
                                    'mode' => 'Omote',
                                    'scored-date' => '2014-08',
                                    'source' => 'Arcadia August 2014',
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[14],
                                    'gameId' => $gameIds[1],
                                    'player' => 'SPS',
                                    'score' => '583,614,753',
                                    'ship' => 'Type A',
                                    'mode' => 'Ura',
                                    'scored-date' => '2014-05-27',
                                    'source' => 'Arcadia September 2014 / [https:// Twitter]',
                                    'comments' => [
                                        '6L 0B remaining',
                                        '1st loop 285m',
                                    ],
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[15],
                                    'gameId' => $gameIds[1],
                                    'player' => 'SPS',
                                    'score' => '481,402,383',
                                    'ship' => 'Type B',
                                    'mode' => 'Omote',
                                    'scored-date' => '2014-11',
                                    'source' => 'Arcadia November 2014',
                                    'comments' => [
                                        '6L 0B remaining',
                                        '1st loop 276m',
                                    ],
                                ]),
                                $this->createScore([
                                    'id' => $scoreIds[16],
                                    'gameId' => $gameIds[1],
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
                            ]);
                        default:
                            return new Scores();
                    }
                }
            ));

        return $scores;
    }

    /**
     * @return int[]
     */
    private function gameIds(): array
    {
        $idGenerator = $this->createIdGenerator();
        return array_map(
            fn () => $this->nextId($idGenerator),
            range(0, 2)
        );
    }

    /**
     * @return int[]
     */
    private function scoreIds(): array
    {
        $idGenerator = $this->createIdGenerator();

        return array_map(
            fn () => $this->nextId($idGenerator),
            range(1, 17)
        );
    }

    /**
     * @return array<string,string>
     */
    private function templates(): array
    {
        return [
            'main' => <<<'TPL'
{{ description }}

{{ include('games') }}
TPL,
            'games' => <<<'TPL'
{% for game in games.all %}
{# Use custom game template where available. #}
{% set customTemplate = "game-#{game.properties.id}" %}
{{ include([customTemplate, 'game']) }}
{% endfor %}

TPL,
            'game' => <<<'TPL'
{| class="wikitable" style="text-align: center"
|-
! colspan="{{ game.headers|length }}" | {{ game.properties.name }}
|-
! {{ game.headers|join(' !! ') }}
{% for score in game.scores %}
{{ include('score') }}
{% endfor %}
|}

TPL,
            'score' => <<<'TPL'
|-
| {% for column in score.columns %}{{ column.value }}{% if not loop.last %} || {% endif %}{% endfor %}
TPL,
        ];
    }

    private function fixedGameTemplate(): string
    {
        return <<<'TPL'
{| class="wikitable" style="text-align: center"
|-
! colspan="6" | [[Great Mahou Daisakusen]]
|-
! Ship !! Score !! Player !! Date / Source !! Comment !! Replay
|-
| rowspan="2" | Birthday
| 83,743,680 || rowspan="2" | Miku || August 2nd, 2020 / [https:// Twitter] || 107 items ||
|-
| 66,693,110 || JHA November 2019 || 107 items ||
|-
| rowspan="2" | Chitta
| 93,664,750 || rowspan="2" | SOF-WTN
| August 8th, 2020 / [https:// Twitter] || 108 items ||
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
