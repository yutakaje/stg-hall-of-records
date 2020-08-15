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
                    'name' => $game->name(),
                    'company' => $game->company(),
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
                                    'player' => $score->player(),
                                    'score' => $score->score(),
                                    'ship' => $score->ship(),
                                    'mode' => $score->mode(),
                                    'weapon' => $score->weapon(),
                                    'scoredDate' => $score->scoredDate(),
                                    'source' => $score->source(),
                                    'comments' => $score->comments(),
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
            $exporter->export($parsedData->layouts())
        );
    }

    private function createParsedData(): ParsedData
    {
        $factory = new ParsedDataFactory();
        return $factory->create(
            $factory->createGlobalProperties(),
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
                        $factory->createScore('ABI', '550,705,999', [
                            'ship' => 'Reco',
                            'mode' => 'Original',
                            'weapon' => 'Normal',
                            'scoredDate' => '2010-02',
                            'source' => 'Blog',
                            'comments' => [
                                '5L 0B remaining',
                                'After stage 4: 273.7m',
                            ],
                        ]),
                        $factory->createScore('ISO / Niboshi', '538,378,364', [
                            'ship' => 'Reco',
                            'mode' => 'Original',
                            'weapon' => 'Normal',
                            'scoredDate' => '2007-10',
                            'source' => 'Arcadia October 2007',
                        ]),
                        $factory->createScore('yasu0219', '454,386,226', [
                            'ship' => 'Reco',
                            'mode' => 'Original',
                            'weapon' => 'Abnormal',
                            'scoredDate' => '2009-12-12',
                            'source' => 'Xbox rankings',
                            'comments' => [
                                'Highest score Xbox360',
                            ],
                        ]),
                        $factory->createScore('KTL-NAL', '981,872,827', [
                            'ship' => 'Palm',
                            'mode' => 'Maniac',
                            'weapon' => 'Abnormal',
                            'scoredDate' => '2007-09',
                            'source' => 'Superplay DVD',
                            'comments' => [
                                '5L 2B remaining',
                                'After stage 4: 693.8m',
                            ],
                        ]),
                        $factory->createScore('KTL-NAL', '973,020,065', [
                            'ship' => 'Palm',
                            'mode' => 'Maniac',
                            'weapon' => 'Abnormal',
                            'scoredDate' => '2007-11',
                            'source' => 'Arcadia November 2007',
                        ]),
                        $factory->createScore('Clover-TAC', '1,047,258,714', [
                            'ship' => 'Reco',
                            'mode' => 'Maniac',
                            'weapon' => 'Normal',
                            'scoredDate' => '2015-03',
                            'source' => 'Arcadia March 2015',
                            'comments' => [
                                '5L 2B remaining',
                                'After stage 4: 745.1m',
                            ],
                        ]),
                        $factory->createScore('rescue_STG', '2,956,728,306', [
                            'ship' => 'Palm',
                            'mode' => 'Ultra',
                            'weapon' => 'Normal',
                            'scoredDate' => '2017-04-08',
                            'source' => 'Xbox rankings',
                            'comments' => [
                                'Highest score Xbox360',
                            ],
                        ]),
                        $factory->createScore('Dame K.K', '3,999,999,999', [
                            'ship' => 'Palm',
                            'mode' => 'Ultra',
                            'weapon' => 'Abnormal',
                            'scoredDate' => '2008-03',
                            'source' => 'Arcadia March 2008',
                            'comments' => [
                                '1L 0B remaining',
                                'Highest score Arcade',
                            ],
                        ]),
                        $factory->createScore('KGM', '3,999,999,999 [4,263,416,356]', [
                            'ship' => 'Palm',
                            'mode' => 'Ultra',
                            'weapon' => 'Abnormal',
                            'scoredDate' => '2013-07-24',
                            'source' => 'Xbox rankings',
                            'comments' => [
                                'Highest score Xbox360',
                            ],
                        ]),
                        $factory->createScore('fufufu', '3,999,999,999', [
                            'ship' => 'Reco',
                            'mode' => 'Ultra',
                            'weapon' => 'Normal',
                            'scoredDate' => '2009-05-27',
                            'source' => 'Arcadia August 2009',
                            'comments' => [
                                '0L 0B remaining',
                                'After stage 4: 2.205b',
                            ],
                        ]),
                        $factory->createScore('lstze', '3,266,405,598', [
                            'ship' => 'Reco',
                            'mode' => 'Ultra',
                            'weapon' => 'Abnormal',
                            'scoredDate' => '2014?',
                        ]),
                    ],
                    $factory->createLayout(
                        [
                            $factory->createColumn('Mode', '{{ mode }}', [
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn('Character', '{{ ship }}', [
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn('Style', '{{ weapon }}'),
                            $factory->createColumn('Score', '{{ score }}'),
                            $factory->createColumn('Player', '{{ player }}', [
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn(
                                'Date / Source',
                                '{{ scored-date }} / {{ source }}'
                            ),
                            $factory->createColumn(
                                'Comments',
                                "{{ comments|join('; ') }}"
                            ),
                        ],
                        [
                            'mode' => ['Original', 'Maniac', 'Ultra'],
                            'ship' => ['Reco', 'Palm'],
                            'weapon' => ['Normal', 'Abnormal'],
                            'score' => 'desc',
                        ]
                    )
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
                        $factory->createScore('SPS', '583,614,753', [
                            'ship' => 'Type A',
                            'mode' => 'Ura',
                            'scoredDate' => '2014-05-27',
                            'source' => 'Arcadia September 2014 / '
                                 . '[https://twitter.com/SPSPUYO/status/471312775843561472 Twitter]',
                            'comments' => [
                                '6L 0B remaining',
                                '1st loop 285m',
                            ],
                        ]),
                        $factory->createScore('SPS', '481,402,383', [
                            'ship' => 'Type B',
                            'mode' => 'Omote',
                            'scoredDate' =>  '2014-11',
                            'source' => 'Arcadia November 2014',
                            'comments' => [
                                '6L 0B remaining',
                                '1st loop 276m',
                            ],
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
                            $factory->createColumn('Ship', '{{ ship }}', [
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn('Loop', '{{ mode }}'),
                            $factory->createColumn('Score', '{{ score }}'),
                            $factory->createColumn('Player', '{{ player }}', [
                                'groupSameValues' => true,
                            ]),
                            $factory->createColumn(
                                'Date / Source',
                                '{{ scored-date }} / {{ source }}'
                            ),
                            $factory->createColumn(
                                'Comments',
                                "{{ comments|join('; ') }}"
                            ),
                        ],
                        [
                            'ship' => 'asc',
                            'mode' => 'asc',
                            'score' => 'desc',
                        ]
                    )
                ),
            ]
        );
    }
}
