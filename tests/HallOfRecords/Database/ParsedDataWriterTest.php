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
use Stg\HallOfRecords\Import\ParsedData;
use Stg\HallOfRecords\Import\ParsedGame;

class ParsedDataWriterTest extends \Tests\TestCase
{
    public function testWrite(): void
    {
        $parsedData = new ParsedData(
            $this->createParsedGlobalProperties([]),
            $this->createParsedGames()
        );

        $connection = $this->prepareDatabase();
        $writer = new ParsedDataWriter($connection);
        $writer->write($parsedData);

        $gameRecords = $this->readGames($connection);
        $scoreRecords = $this->readScores($connection);

        self::assertEquals($parsedData->games(), array_map(
            fn (array $gameRecord) => $this->createParsedGame([
                'name' => $gameRecord['name'],
                'company' => $gameRecord['company'],
                'scores' => array_map(
                    fn (array $scoreRecord) => $this->createParsedScore([
                        'player' => $scoreRecord['player'],
                        'score' => $scoreRecord['score'],
                        'ship' => $scoreRecord['ship'],
                        'mode' => $scoreRecord['mode'],
                        'weapon' => $scoreRecord['weapon'],
                        'scoredDate' => $scoreRecord['scored_date'],
                        'source' => $scoreRecord['source'],
                        'comments' => json_decode($scoreRecord['comments']),
                    ]),
                    $this->filterByGame($scoreRecords, $gameRecord['id'])
                ),
            ]),
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

    /**
     * @return ParsedGame[]
     */
    private function createParsedGames(): array
    {
        return [
            $this->createParsedGame([
                'name' => 'Mushihimesama Futari 1.5',
                'company' => 'Cave',
                'scores' => [
                    $this->createParsedScore([
                        'player' => 'ABI',
                        'score' => '530,358,660',
                        'ship' => 'Palm',
                        'mode' => 'Original',
                        'weapon' => 'Normal',
                        'scoredDate' => '2008-01',
                        'source' => 'Arcadia January 2008',
                    ]),
                    $this->createParsedScore([
                        'player' => 'ISO / Niboshi',
                        'score' => '518,902,716',
                        'ship' => 'Palm',
                        'mode' => 'Original',
                        'weapon' => 'Abnormal',
                        'scoredDate' => '2007',
                        'source' => 'Superplay DVD',
                    ]),
                ],
            ]),
            $this->createParsedGame([
                'name' => 'Ketsui: Kizuna Jigoku Tachi',
                'company' => 'ケイブ',
                'scores' => [
                    $this->createParsedScore([
                        'player' => 'SPS',
                        'score' => '507,780,433',
                        'ship' => 'Type A',
                        'mode' => 'Omote',
                        'scoredDate' => '2014-08',
                        'source' => 'Arcadia August 2014',
                        'comments' => [],
                    ]),
                    $this->createParsedScore([
                        'player' => 'GAN',
                        'score' => '569,741,232',
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
            ]),
        ];
    }
}
