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

namespace Tests\HallOfRecords\Data\Score;

use Stg\HallOfRecords\Data\ArrayGrouper;
use Stg\HallOfRecords\Data\ItemInterface;

class ArrayGrouperTest extends \Tests\TestCase
{
    public function testWithEmptyArray(): void
    {
        $items = [
            $this->createItem(10, 'Type A', 'easy'),
            $this->createItem(100, 'Type A', 'normal'),
            $this->createItem(120, 'Type B', 'normal'),
            $this->createItem(150, 'Type B', 'normal'),
            $this->createItem(9, 'Type B', 'hard'),
            $this->createItem(14, 'Type C', 'hard'),
        ];

        $grouper = new ArrayGrouper();

        self::assertEquals(
            [$items],
            $grouper->group($items, [])
        );
    }

    public function testShip(): void
    {
        $items = [
            $this->createItem(10, 'Type A', 'easy'),
            $this->createItem(100, 'Type A', 'normal'),
            $this->createItem(120, 'Type B', 'normal'),
            $this->createItem(150, 'Type B', 'normal'),
            $this->createItem(9, 'Type B', 'hard'),
            $this->createItem(14, 'Type C', 'hard'),
        ];

        $grouper = new ArrayGrouper();

        self::assertEquals(
            [
                [
                    $items[0],
                    $items[1],
                ],
                [
                    $items[2],
                    $items[3],
                    $items[4],
                ],
                [
                    $items[5],
                ],
            ],
            $grouper->group($items, [
                'ship',
            ])
        );
    }

    public function testGroupByShipAndDifficulty(): void
    {
        $items = [
            $this->createItem(10, 'Type A', 'easy'),
            $this->createItem(100, 'Type A', 'normal'),
            $this->createItem(120, 'Type B', 'normal'),
            $this->createItem(150, 'Type B', 'normal'),
            $this->createItem(9, 'Type B', 'hard'),
            $this->createItem(14, 'Type C', 'hard'),
        ];

        $grouper = new ArrayGrouper();

        self::assertEquals(
            [
                [
                    $items[0],
                ],
                [
                    $items[1],
                ],
                [
                    $items[2],
                    $items[3],
                ],
                [
                    $items[4],
                ],
                [
                    $items[5],
                ],
            ],
            $grouper->group($items, [
                'ship',
                'difficulty',
            ])
        );
    }

    private function createItem(
        int $id,
        string $ship,
        string $difficulty
    ): ItemInterface {
        return new class ($id, $ship, $difficulty) implements ItemInterface {
            private int $id;
            private string $ship;
            private string $difficulty;

            public function __construct(
                int $id,
                string $ship,
                string $difficulty
            ) {
                $this->id = $id;
                $this->ship = $ship;
                $this->difficulty = $difficulty;
            }

            public function id(): int
            {
                return $this->id;
            }

            public function ship(): string
            {
                return $this->ship;
            }

            public function difficulty(): string
            {
                return $this->difficulty;
            }

            /**
             * @return mixed
             */
            public function property(string $ship)
            {
                switch ($ship) {
                    case 'id':
                        return $this->id;
                    case 'ship':
                        return $this->ship;
                    case 'difficulty':
                        return $this->difficulty;
                    default:
                        return null;
                }
            }
        };
    }
}
