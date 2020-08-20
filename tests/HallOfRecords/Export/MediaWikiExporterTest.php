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

use Stg\HallOfRecords\Export\MediaWikiExporter;
use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Games;
use Stg\HallOfRecords\Data\ScoreRepositoryInterface;
use Stg\HallOfRecords\Data\Scores;
use Stg\HallOfRecords\Import\ParsedDataFactory;
use Stg\HallOfRecords\Import\ParsedData;
use Stg\HallOfRecords\Import\ParsedGame;
use Stg\HallOfRecords\Import\ParsedScore;

class MediaWikiExporterTest extends \Tests\TestCase
{
    public function testExport(): void
    {
        $parsedData = $this->createParsedData();

        $gameRepository = $this->createMock(GameRepositoryInterface::class);
        $gameRepository->method('all')
            ->willReturn(new Games(array_map(
                fn (ParsedGame $game) => $this->createGame([
                    'id' => $game->id(),
                    'name' => $game->getProperty('name'),
                    'company' => $game->getProperty('company'),
                ]),
                $parsedData->games()
            )));

        $scoreRepository = $this->createMock(ScoreRepositoryInterface::class);
        $scoreRepository->method('filterByGame')
            ->will(self::returnCallback(
                function (Game $game) use ($parsedData): Scores {
                    $scores = [];
                    foreach ($parsedData->games() as $parsedGame) {
                        if ($parsedGame->id() === $game->id()) {
                            $scores = array_map(
                                fn (ParsedScore $score) => $this->createScore([
                                    'id' => $score->id(),
                                    'gameId' => $game->id(),
                                    'player' => $score->getProperty('player'),
                                    'score' => $score->getProperty('score'),
                                    'ship' => $score->getProperty('ship'),
                                    'mode' => $score->getProperty('mode'),
                                    'weapon' => $score->getProperty('weapon'),
                                    'scoredDate' => $score->getProperty('scored-date'),
                                    'source' => $score->getProperty('source'),
                                    'comments' => $score->getProperty('comments'),
                                ]),
                                $parsedGame->scores()
                            );
                            break;
                        }
                    }
                    return new Scores($scores);
                }
            ));

        $exporter = new MediaWikiExporter($gameRepository, $scoreRepository);

        self::assertSame(
            $this->loadFile(__DIR__ . '/media-wiki-output-en'),
            $exporter->export(
                $parsedData->layouts(),
                $parsedData->globalProperties()->getProperty('templates')
            )
        );
    }

    private function createParsedData(): ParsedData
    {
        $factory = new ParsedDataFactory();
        return $factory->create(
            $factory->createGlobalProperties([
                'templates' => [
                    'games' => <<<'TPL'
{% for game in games %}
{% if game.template %}
{{ game.template|raw }}
{% else %}
{{ include('game') }}
{% endif %}
{% endfor %}

TPL,
                    'game' => <<<'TPL'
{| class="wikitable" style="text-align: center"
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
                        $factory->createScore([
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
                        $factory->createScore([
                            'player' => 'ISO / Niboshi',
                            'score' => '538,378,364',
                            'ship' => 'Reco',
                            'mode' => 'Original',
                            'weapon' => 'Normal',
                            'scored-date' => '2007-10',
                            'source' => 'Arcadia October 2007',
                        ]),
                        $factory->createScore([
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
                        $factory->createScore([
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
                        $factory->createScore([
                            'player' => 'KTL-NAL',
                            'score' => '973,020,065',
                            'ship' => 'Palm',
                            'mode' => 'Maniac',
                            'weapon' => 'Abnormal',
                            'scored-date' => '2007-11',
                            'source' => 'Arcadia November 2007',
                        ]),
                        $factory->createScore([
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
                        $factory->createScore([
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
                        $factory->createScore([
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
                        ]),
                        $factory->createScore([
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
                        $factory->createScore([
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
                        $factory->createScore([
                            'player' => 'lstze',
                            'score' => '3,266,405,598',
                            'ship' => 'Reco',
                            'mode' => 'Ultra',
                            'weapon' => 'Abnormal',
                            'scored-date' => '2014?',
                        ]),
                    ],
                    $factory->createLayout([
                        'columns' => [
                            $factory->createColumn([
                                'label' => 'Mode',
                                'template' => '{{ mode }}',
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn([
                                'label' => 'Character',
                                'template' => '{{ ship }}',
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn([
                                'label' => 'Style',
                                'template' => '{{ weapon }}',
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
                                'label' => 'Comments',
                                'template' => "{{ comments|join('; ') }}",
                            ]),
                        ],
                        'sort' => [
                            'mode' => ['Original', 'Maniac', 'Ultra'],
                            'ship' => ['Reco', 'Palm'],
                            'weapon' => ['Normal', 'Abnormal'],
                            'score' => 'desc',
                        ],
                    ])
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
                        ]),
                        $factory->createScore([
                            'player' => 'SPS',
                            'score' => '583,614,753',
                            'ship' => 'Type A',
                            'mode' => 'Ura',
                            'scored-date' => '2014-05-27',
                            'source' => 'Arcadia September 2014 / '
                                 . '[https://twitter.com/SPSPUYO/status/471312775843561472 Twitter]',
                            'comments' => [
                                '6L 0B remaining',
                                '1st loop 285m',
                            ],
                        ]),
                        $factory->createScore([
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
                                'label' => 'Comments',
                                'template' => "{{ comments|join('; ') }}",
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
            ]
        );
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
