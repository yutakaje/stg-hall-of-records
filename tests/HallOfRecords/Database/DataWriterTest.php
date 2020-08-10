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
use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\Scores;
use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\Games;
use Stg\HallOfRecords\Database\ConnectionFactory;
use Stg\HallOfRecords\Database\DataWriter;
use Stg\HallOfRecords\Database\InMemoryDatabaseCreator;
use Stg\HallOfRecords\Import\YamlParser;

class DataWriterTest extends \Tests\TestCase
{
    public function testWithGamesJpLocale(): void
    {
        $locale = 'jp';
        $parser = $this->parseInput($locale);

        $games = $parser->games();

        $connection = $this->prepareDatabase();
        $importer = new DataWriter($connection);
        $importer->import($games);

        $this->assertGames($connection, $this->sortGames($games));
        $this->assertScores($connection, $this->sortScores($games));
    }

    /**
     * @param Game[] $expected
     */
    private function assertGames(Connection $connection, array $expected): void
    {
        $records = $connection->createQueryBuilder()
            ->select('name', 'company')
            ->from('games')
            ->orderBy('name')
            ->execute()
            ->fetchAll();

        self::assertSame(
            array_map(
                fn (array $record) => [
                    'name' => $record['name'],
                    'company' => $record['company'],
                ],
                $records
            ),
            array_map(
                fn (Game $game) => [
                    'name' => $game->name(),
                    'company' => $game->company(),
                ],
                $expected
            )
        );
    }

    /**
     * @param Score[] $expected
     */
    private function assertScores(Connection $connection, array $expected): void
    {
        $records = $connection->createQueryBuilder()
            ->select('player', 'score')
            ->from('scores')
            ->orderBy('game')
            ->addOrderBy('player')
            ->addOrderBy('score')
            ->execute()
            ->fetchAll();

        self::assertSame(
            array_map(
                fn (array $record) => [
                    'player' => $record['player'],
                    'score' => $record['score'],
                ],
                $records
            ),
            array_map(
                fn (Score $score) => [
                    'player' => $score->player(),
                    'score' => $score->score(),
                ],
                $expected
            )
        );
    }

    /**
     * @return Game[]
     */
    private function sortGames(Games $games): array
    {
        return [
            $this->gameAtIndex($games, 1),
            $this->gameAtIndex($games, 0),
        ];
    }

    /**
     * @return Score[]
     */
    private function sortScores(Games $games): array
    {
        return [
            $this->scoreAtIndex($this->gameAtIndex($games, 1)->scores(), 1),
            $this->scoreAtIndex($this->gameAtIndex($games, 1)->scores(), 0),
            $this->scoreAtIndex($this->gameAtIndex($games, 0)->scores(), 0),
            $this->scoreAtIndex($this->gameAtIndex($games, 0)->scores(), 1),
        ];
    }

    private function gameAtIndex(Games $games, int $index): Game
    {
        $iterator = $games->iterator();

        if (!isset($iterator[$index])) {
            throw new \InvalidArgumentException(
                "No game found at index `{$index}`"
            );
        }

        return $iterator[$index];
    }

    private function scoreAtIndex(Scores $scores, int $index): Score
    {
        $iterator = $scores->iterator();

        if (!isset($iterator[$index])) {
            throw new \InvalidArgumentException(
                "No score found at index `{$index}`"
            );
        }

        return $iterator[$index];
    }

    private function parseInput(string $locale): YamlParser
    {
        $global = $this->globalPropertiesInput();
        $games = $this->gamesInput();

        $parser = new YamlParser($locale);
        $parser->parse(array_merge(
            [$global],
            $games
        ));
        return $parser;
    }

    private function prepareDatabase(): Connection
    {
        $connection = (new ConnectionFactory())->create();
        $dbCreator = new InMemoryDatabaseCreator($connection);
        $dbCreator->create();
        return $connection;
    }

    /**
     * @return array<string,mixed>
     */
    private function globalPropertiesInput(): array
    {
        return [
            'name' => 'global',
            'translations' => [
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
                'translations' => [
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
