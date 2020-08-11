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
use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\Games;
use Stg\HallOfRecords\Data\Scores;
use Stg\HallOfRecords\Database\DataReader;

class DataReaderTest extends \Tests\TestCase
{
    public function testWithGamesJpLocale(): void
    {
        $connection = $this->prepareDatabase();
        $games = $this->createGames();

        $this->insertGames($connection, $games);

        $writer = new DataReader($connection);

        self::assertEquals($games, $writer->read());
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
                'company' => 'Cave',
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

    private function insertGames(Connection $connection, Games $games): void
    {
        $qb = $connection->createQueryBuilder();

        foreach ($games->iterator() as $game) {
            $qb->insert('games')
                ->values([
                    'id' => ':id',
                    'name' => ':name',
                    'company' => ':company',
                ])
                ->setParameter(':id', $game->id())
                ->setParameter(':name', $game->name())
                ->setParameter(':company', $game->company())
                ->execute();

            $this->insertScores($connection, $game);
        }
    }

    public function insertScores(Connection $connection, Game $game): void
    {
        $qb = $connection->createQueryBuilder();

        foreach ($game->scores()->iterator() as $score) {
            $qb->insert('scores')
                ->values([
                    'id' => ':id',
                    'game_id' => ':gameId',
                    'player' => ':player',
                    'score' => ':score',
                    'ship' => ':ship',
                    'mode' => ':mode',
                    'weapon' => ':weapon',
                    'scored_date' => ':scoredDate',
                    'source' => ':source',
                    'comments' => ':comments',
                ])
                ->setParameter(':id', $score->id())
                ->setParameter(':gameId', $game->id())
                ->setParameter(':player', $score->player())
                ->setParameter(':score', $score->score())
                ->setParameter(':ship', $score->ship())
                ->setParameter(':mode', $score->mode())
                ->setParameter(':weapon', $score->weapon())
                ->setParameter(':scoredDate', $score->scoredDate())
                ->setParameter(':source', $score->source())
                ->setParameter(':comments', json_encode($score->comments()))
                ->execute();
        }
    }
}
