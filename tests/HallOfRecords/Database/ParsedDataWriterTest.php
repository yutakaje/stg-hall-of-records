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

namespace Tests\HallOfRecords\Database;

use Doctrine\DBAL\Connection;
use Stg\HallOfRecords\Database\ParsedDataWriter;
use Stg\HallOfRecords\Import\ParsedDataFactory;
use Stg\HallOfRecords\Import\ParsedData;

class ParsedDataWriterTest extends \Tests\TestCase
{
    public function testWrite(): void
    {
        $parsedData = $this->createParsedData();

        $connection = $this->prepareDatabase();
        $writer = new ParsedDataWriter($connection);
        $writer->write($parsedData);

        $gameRecords = $this->readGames($connection);
        $scoreRecords = $this->readScores($connection);

        $factory = new ParsedDataFactory();

        self::assertEquals($parsedData->games(), array_map(
            fn (array $gameRecord) => $factory->createGame(
                $gameRecord['name'],
                $gameRecord['company'],
                array_map(
                    fn (array $scoreRecord) => $factory->createScore(
                        $scoreRecord['player'],
                        $scoreRecord['score'],
                        [
                            'ship' => $scoreRecord['ship'],
                            'mode' => $scoreRecord['mode'],
                            'weapon' => $scoreRecord['weapon'],
                            'scoredDate' => $scoreRecord['scored_date'],
                            'source' => $scoreRecord['source'],
                            'comments' => json_decode($scoreRecord['comments']),
                        ]
                    ),
                    $this->filterByGame($scoreRecords, $gameRecord['id'])
                ),
                $factory->createLayout()
            ),
            $gameRecords
        ));
    }

    /**
     * @return array<string,string>[]
     */
    private function readGames(Connection $connection): array
    {
        return $connection->createQueryBuilder()
            ->select('id', 'name', 'company')
            ->from('games')
            ->orderBy('id')
            ->execute()
            ->fetchAll();
    }

    /**
     * @return array<string,string>[]
     */
    private function readScores(Connection $connection): array
    {
        return $connection->createQueryBuilder()
            ->select(
                'id',
                'game_id',
                'player',
                'score',
                'ship',
                'mode',
                'weapon',
                'scored_date',
                'source',
                'comments'
            )
            ->from('scores')
            ->orderBy('id')
            ->execute()
            ->fetchAll();
    }

    /**
     * @param array<string,string>[] $scoreRecords
     * @return array<string,string>[]
     */
    private function filterByGame(array $scoreRecords, string $gameId): array
    {
        return array_values(array_filter(
            $scoreRecords,
            fn (array $record) => $record['game_id'] === $gameId
        ));
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
                    ],
                    $factory->createLayout()
                ),
                $factory->createGame(
                    'Ketsui: Kizuna Jigoku Tachi',
                    'ケイブ',
                    [
                        $factory->createScore('SPS', '507,780,433', [
                            'ship' => 'Type A',
                            'mode' => 'Omote',
                            'scoredDate' => '2014-08',
                            'source' => 'Arcadia August 2014',
                            'comments' => [],
                        ]),
                        $factory->createScore('GAN', '569,741,232', [
                            'ship' => 'Type B',
                            'mode' => 'Ura',
                            'scoredDate' => '2016-03',
                            'source' => 'JHA March 2016',
                            'comments' => [
                                '残6機',
                                '1周 2.85億',
                            ],
                        ]),
                    ],
                    $factory->createLayout()
                ),
            ]
        );
    }
}
