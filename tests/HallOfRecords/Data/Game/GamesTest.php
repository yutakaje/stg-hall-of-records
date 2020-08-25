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

namespace Tests\HallOfRecords\Data\Game;

use Stg\HallOfRecords\Data\Game\Games;

class GamesTest extends \Tests\TestCase
{
    public function testSortWithEmptyArray(): void
    {
        $games = $this->createGames();

        self::assertEquals(
            $this->sortGames($games, [0, 1, 2, 3]),
            $games->sort([])
        );
    }

    public function testSortByNameAsc(): void
    {
        $games = $this->createGames();

        self::assertEquals(
            $this->sortGames($games, [1, 3, 2, 0]),
            $games->sort([
                'name' => 'asc',
            ])
        );
    }

    public function testSortByCompanyDescNameAsc(): void
    {
        $games = $this->createGames();

        self::assertEquals(
            $this->sortGames($games, [1, 3, 2, 0]),
            $games->sort([
                'company' => 'desc',
                'name' => 'asc',
            ])
        );
    }

    public function testSortByCustomOrder(): void
    {
        $games = $this->createGames();

        self::assertEquals(
            $this->sortGames($games, [1, 0, 2, 3]),
            $games->sort([
                'company' => [
                    'Raizing',
                    'Cave',
                    'Konami',
                ],
                'name' => 'desc',
            ])
        );
    }

    public function testSortWithInvalidProperty(): void
    {
        $games = $this->createGames();

        // Invalid properties should be ignored.
        self::assertEquals(
            $this->sortGames($games, [0, 1, 2, 3]),
            $games->sort([
                'bad-property' => 'asc',
            ])
        );
    }

    private function createGames(): Games
    {
        return new Games([
            $this->createGame([
                'name' => 'Mushihimesama Futari 1.5',
                'company' => 'Cave',
            ]),
            $this->createGame([
                'name' => 'Battle Garegga',
                'company' => 'Raizing',
            ]),
            $this->createGame([
                'name' => 'Ketsui: Kizuna Jigoku Tachi',
                'company' => 'Cave',
            ]),
            $this->createGame([
                'name' => 'Gradius',
                'company' => 'Konami',
            ]),
        ]);
    }

    /**
     * @param int[] $order
     */
    private function sortGames(Games $games, array $order): Games
    {
        $unsorted = $games->asArray();

        return new Games(array_map(
            fn (int $index) => $unsorted[$index],
            $order
        ));
    }
}
