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

use Stg\HallOfRecords\Data\ArraySorter;
use Stg\HallOfRecords\Data\ItemInterface;

class ArraySorterTest extends \Tests\TestCase
{
    public function testWithEmptyArray(): void
    {
        $items = [
            $this->createItem(10, 'Ship', ['difficulty' => 'easy']),
            $this->createItem(100, 'Loop', ['difficulty' => 'easy']),
            $this->createItem(120, 'score', ['difficulty' => 'normal']),
            $this->createItem(9, 'Player', ['difficulty' => 'normal']),
            $this->createItem(14, 'Comment', ['difficulty' => 'hard']),
        ];

        $sorter = new ArraySorter();

        self::assertSame(
            $items,
            $sorter->sort($items, [])
        );
    }

    public function testWithEnLocale(): void
    {
        $items = [
            $this->createItem(10, 'Ship', ['difficulty' => 'easy']),
            $this->createItem(100, 'Loop', ['difficulty' => 'easy']),
            $this->createItem(120, 'score', ['difficulty' => 'normal']),
            $this->createItem(9, 'Player', ['difficulty' => 'normal']),
            $this->createItem(14, 'Comment', ['difficulty' => 'hard']),
        ];

        $sorter = new ArraySorter();

        self::assertSame(
            [9, 10, 14, 100, 120],
            array_map(
                fn ($item) => $item->id(),
                $sorter->sort($items, [
                    'id' => 'asc',
                ])
            ),
            'Sort by id asc'
        );
        self::assertSame(
            ['Ship', 'score', 'Player', 'Loop', 'Comment'],
            array_map(
                fn ($item) => $item->name(),
                $sorter->sort($items, [
                    'name' => 'desc',
                ])
            ),
            'Sort by name desc'
        );
        self::assertSame(
            [100, 10, 14, 120, 9],
            array_map(
                fn ($item) => $item->id(),
                $sorter->sort($items, [
                    'difficulty' => 'asc',
                    'id' => 'desc',
                ])
            ),
            'Sort by difficulty asc, id desc'
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
            ),
            'Sort by custom order, id asc'
        );
    }

    public function testWithJpLocale(): void
    {
        $items = [
            $this->createItem(10, 'ゆたか', ['difficulty' => 'イージー']),
            $this->createItem(100, 'かすかべ', ['difficulty' => 'イージー']),
            $this->createItem(120, 'たては', ['difficulty' => 'ノーマル']),
            $this->createItem(9, 'しんどう', ['difficulty' => 'ノーマル']),
            $this->createItem(14, 'うおたろう', ['difficulty' => 'ハード']),
        ];

        $sorter = new ArraySorter();

        self::assertSame(
            [9, 10, 14, 100, 120],
            array_map(
                fn ($item) => $item->id(),
                $sorter->sort($items, [
                    'id' => 'asc',
                ])
            ),
            'Sort by id asc'
        );
        self::assertSame(
            ['ゆたか', 'たては', 'しんどう', 'かすかべ', 'うおたろう'],
            array_map(
                fn ($item) => $item->name(),
                $sorter->sort($items, [
                    'name' => 'desc',
                ])
            ),
            'Sort by name desc'
        );
        self::assertSame(
            [100, 10, 120, 9, 14],
            array_map(
                fn ($item) => $item->id(),
                $sorter->sort($items, [
                    'difficulty' => 'asc',
                    'id' => 'desc',
                ])
            ),
            'Sort by difficulty asc, id desc'
        );

        self::assertSame(
            [14, 10,100, 9, 120],
            array_map(
                fn ($item) => $item->id(),
                $sorter->sort($items, [
                    'difficulty' => [
                        'ハード',
                        'イージー',
                        'ノーマル',
                    ],
                    'id' => 'asc',
                ])
            ),
            'Sort by custom order, id asc'
        );
    }

    public function testWithJpLocaleKanji(): void
    {
        $items = [
            $this->createItem(38, 'ケツイ ～絆地獄たち～', ['name-sort' => 'けつい ～きずなじごくたち～']),
            $this->createItem(14, '虫姫さまふたりVer 1.5', ['name-sort' => 'むしひめさまふたりVer 1.5']),
            $this->createItem(120, '赤い刀', ['name-sort' => 'あかいかたな']),
        ];

        $sorter = new ArraySorter();

        self::assertSame(
            [120, 38, 14],
            array_map(
                fn ($item) => $item->id(),
                $sorter->sort($items, [
                    'name' => 'asc',
                ])
            ),
            'Sort by name (kana)'
        );
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function createItem(
        int $id,
        string $name,
        array $properties = []
    ): ArrayItem {
        return new ArrayItem($id, $name, $properties);
    }
}
