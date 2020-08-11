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

namespace Tests\HallOfRecords\Database;

use Doctrine\DBAL\Connection;
use Stg\HallOfRecords\Data\Games;
use Stg\HallOfRecords\Data\Scores;
use Stg\HallOfRecords\Database\DataWriter;

class DataWriterTest extends \Tests\TestCase
{
    public function testWithGamesJpLocale(): void
    {
        $games = $this->createGames();

        $connection = $this->prepareDatabase();
        $writer = new DataWriter($connection);
        $writer->write($games);

        $gameRecords = $this->readGames($connection);
        $scoreRecords = $this->readScores($connection);

        self::assertEquals($games, new Games(array_map(
            fn (array $gameRecord) => $this->createGame([
                'id' => (int)$gameRecord['id'],
                'name' => $gameRecord['name'],
                'company' => $gameRecord['company'],
                'scores' => new Scores(array_map(
                    fn (array $scoreRecord) => $this->createScore([
                        'id' => (int)$scoreRecord['id'],
                        'player' => $scoreRecord['player'],
                        'score' => $scoreRecord['score'],
                        'ship' => $scoreRecord['ship'],
                        'mode' => $scoreRecord['mode'],
                        'weapon' => $scoreRecord['weapon'],
                        'scored-date' => $scoreRecord['scored_date'],
                        'source' => $scoreRecord['source'],
                        'comments' => json_decode($scoreRecord['comments']),
                    ]),
                    $this->filterByGame($scoreRecords, $gameRecord['id'])
                )),
            ]),
            $gameRecords
        )));
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

    private function createGames(): Games
    {
        return new Games([
            $this->createGame([
                'id' => 10,
                'name' => 'Mushihimesama Futari 1.5',
                'company' => 'Cave',
                'scores' => new Scores([
                    $this->createScore([
                        'id' => 21,
                        'player' => 'ABI',
                        'score' => '530,358,660',
                        'ship' => 'Palm',
                        'mode' => 'Original',
                        'weapon' => 'Normal',
                        'scored-date' => '2008-01',
                        'source' => 'Arcadia January 2008',
                    ]),
                    $this->createScore([
                        'id' => 23,
                        'player' => 'ISO / Niboshi',
                        'score' => '518,902,716',
                        'ship' => 'Palm',
                        'mode' => 'Original',
                        'weapon' => 'Abnormal',
                        'scored-date' => '2007',
                        'source' => 'Superplay DVD',
                    ]),
                ]),
            ]),
            $this->createGame([
                'id' => 23,
                'name' => 'Ketsui: Kizuna Jigoku Tachi',
                'company' => 'ケイブ',
                'scores' => new Scores([
                    $this->createScore([
                        'id' => 25,
                        'player' => 'SPS',
                        'score' => '507,780,433',
                        'ship' => 'Type A',
                        'mode' => 'Omote',
                        'scored-date' => '2014-08',
                        'source' => 'Arcadia August 2014',
                        'comments' => [],
                    ]),
                    $this->createScore([
                        'id' => 28,
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
                    ]),
                ]),
            ]),
        ]);
    }
}
