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

use Stg\HallOfRecords\Data\Sorting\ArraySorter;
use Stg\HallOfRecords\Data\Sorting\SortableInterface;

class ArraySorterTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        $items = [
            $this->createItem(10, 'Ship', 'easy'),
            $this->createItem(100, 'Loop', 'easy'),
            $this->createItem(120, 'Score', 'normal'),
            $this->createItem(9, 'Player', 'normal'),
            $this->createItem(14, 'Comment', 'hard'),
        ];

        $sorter = new ArraySorter();

        self::assertSame(
            [9, 10, 14, 100, 120],
            array_map(
                fn ($item) => $item->id(),
                $sorter->sort($items, [
                    'id' => 'asc',
                ])
            )
        );
        self::assertSame(
            ['Ship', 'Score', 'Player', 'Loop', 'Comment'],
            array_map(
                fn ($item) => $item->name(),
                $sorter->sort($items, [
                    'name' => 'desc',
                ])
            )
        );
        self::assertSame(
            [100, 10, 14, 120, 9],
            array_map(
                fn ($item) => $item->id(),
                $sorter->sort($items, [
                    'difficulty' => 'asc',
                    'id' => 'desc',
                ])
            )
        );

        self::assertSame(
            [14, 10,100, 9, 120],
            array_map(
                fn ($item) => $item->id(),
                $sorter->sort($items, [
                    'difficulty' => [
                        'hard',
                        'easy',
                        'normal',
                    ],
                    'id' => 'asc',
                ])
            )
        );
    }

    private function createItem(
        int $id,
        string $name,
        string $difficulty
    ): SortableInterface {
        return new class ($id, $name, $difficulty) implements SortableInterface {
            private int $id;
            private string $name;
            private string $difficulty;

            public function __construct(
                int $id,
                string $name,
                string $difficulty
            ) {
                $this->id = $id;
                $this->name = $name;
                $this->difficulty = $difficulty;
            }

            public function id(): int
            {
                return $this->id;
            }

            public function name(): string
            {
                return $this->name;
            }

            public function difficulty(): string
            {
                return $this->difficulty;
            }

            /**
             * @return mixed
             */
            public function getProperty(string $name)
            {
                switch ($name) {
                    case 'id':
                        return $this->id;
                    case 'name':
                        return $this->name;
                    case 'difficulty':
                        return $this->difficulty;
                    default:
                        return null;
                }
            }
        };
    }
}
