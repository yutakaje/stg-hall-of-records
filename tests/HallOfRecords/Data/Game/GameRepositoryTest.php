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

use Stg\HallOfRecords\Data\Game\Game;
use Stg\HallOfRecords\Data\Game\GameRepository;
use Stg\HallOfRecords\Data\Game\Games;

class GameRepositoryTest extends \Tests\TestCase
{
    public function testAllWithNoGames(): void
    {
        $repository = new GameRepository();

        self::assertEquals(new Games(), $repository->all());
    }

    public function testAll(): void
    {
        $games = $this->createGames();

        $repository = new GameRepository();
        $games->apply($this->addToRepository($repository));

        self::assertEquals($games, $repository->all());
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
     * @return \Closure(Game):void
     */
    private function addToRepository(GameRepository $repository): \Closure
    {
        return function (Game $game) use ($repository): void {
            $repository->add($game);
        };
    }
}
