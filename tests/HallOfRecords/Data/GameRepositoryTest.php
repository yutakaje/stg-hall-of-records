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

namespace Tests\HallOfRecords\Data;

use Stg\HallOfRecords\Data\GameRepository;
use Stg\HallOfRecords\Data\Games;

class GameRepositoryTest extends \Tests\TestCase
{
    public function testAllWithNoGames(): void
    {
        $repository = new GameRepository();

        self::assertEquals(new Games([]), $repository->all());
    }

    public function testAllWithDefaultSort(): void
    {
        $games = $this->createGames();

        $repository = new GameRepository();
        foreach ($games->asArray() as $game) {
            $repository->add($game);
        }

        self::assertEquals(
            $this->sortGames($games, [0, 1, 2, 3]),
            $repository->all()
        );
    }

    public function testAllSortByNameAsc(): void
    {
        $games = $this->createGames();

        $repository = new GameRepository();
        foreach ($games->asArray() as $game) {
            $repository->add($game);
        }

        self::assertEquals(
            $this->sortGames($games, [1, 3, 2, 0]),
            $repository->all([
                'name' => 'asc',
            ])
        );
    }

    public function testAllSortByCompanyDescNameAsc(): void
    {
        $games = $this->createGames();

        $repository = new GameRepository();
        foreach ($games->asArray() as $game) {
            $repository->add($game);
        }

        self::assertEquals(
            $this->sortGames($games, [1, 3, 2, 0]),
            $repository->all([
                'company' => 'desc',
                'name' => 'asc',
            ])
        );
    }

    public function testAllSortByCustomOrder(): void
    {
        $games = $this->createGames();

        $repository = new GameRepository();
        foreach ($games->asArray() as $game) {
            $repository->add($game);
        }

        self::assertEquals(
            $this->sortGames($games, [1, 0, 2, 3]),
            $repository->all([
                'company' => [
                    'Raizing',
                    'Cave',
                    'Konami',
                ],
                'name' => 'desc',
            ])
        );
    }

    public function testAllWithInvalidSort(): void
    {
        $games = $this->createGames();

        $repository = new GameRepository();
        foreach ($games->asArray() as $game) {
            $repository->add($game);
        }

        // Invalid columns should be ignored.
        self::assertEquals(
            $this->sortGames($games, [0, 1, 2, 3]),
            $repository->all([
                'bad_column' => 'asc',
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
