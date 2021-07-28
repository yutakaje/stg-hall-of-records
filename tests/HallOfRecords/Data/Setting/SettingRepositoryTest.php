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

namespace Tests\HallOfRecords\Data\Setting;

use Stg\HallOfRecords\Data\Setting\GameSetting;
use Stg\HallOfRecords\Data\Setting\GlobalSetting;
use Stg\HallOfRecords\Data\Setting\Setting;
use Stg\HallOfRecords\Data\Setting\SettingRepository;
use Stg\HallOfRecords\Data\Setting\Settings;

class SettingRepositoryTest extends \Tests\TestCase
{
    public function testWithNoSettings(): void
    {
        $repository = new SettingRepository();

        self::assertEquals(new Settings([]), $repository->filterGlobal());
        self::assertEquals(new Settings([]), $repository->filterByGame(1));
        self::assertEquals(new Settings([]), $repository->filterByGame(2));
        self::assertEquals(new Settings([]), $repository->filterByGame(99));
    }

    public function testFilterGlobal(): void
    {
        $globalSettings = $this->createGlobalSettings();

        $repository = new SettingRepository();
        $globalSettings->apply($this->addToRepository($repository));
        $this->createGameSettings()->apply($this->addToRepository($repository));

        self::assertEquals($globalSettings, $repository->filterGlobal());
    }

    public function testFilterByGame(): void
    {
        $gameSettings = $this->createGameSettings();

        $repository = new SettingRepository();
        $this->createGlobalSettings()->apply($this->addToRepository($repository));
        $gameSettings->apply($this->addToRepository($repository));

        self::assertEquals(
            new Settings([
                $gameSettings->asArray()[0],
            ]),
            $repository->filterByGame(1)
        );
        self::assertEquals(
            new Settings([
                $gameSettings->asArray()[1],
                $gameSettings->asArray()[2],
            ]),
            $repository->filterByGame(2)
        );
        self::assertEquals(new Settings([]), $repository->filterByGame(99));
    }

    private function createGlobalSettings(): Settings
    {
        return new Settings([
            $this->createGlobalSetting([
                'name' => 'setting a',
                'company' => 'value a',
            ]),
            $this->createGlobalSetting([
                'name' => 'setting a',
                'company' => 'value b',
            ]),
        ]);
    }

    private function createGameSettings(): Settings
    {
        return new Settings([
            $this->createGameSetting([
                'gameId' => 1,
                'name' => 'template',
                'company' => 'template 1',
            ]),
            $this->createGameSetting([
                'gameId' => 2,
                'name' => 'template',
                'company' => 'template 2',
            ]),
            $this->createGameSetting([
                'gameId' => 2,
                'name' => 'layout',
                'company' => 'columns',
            ]),
        ]);
    }

    /**
     * @return \Closure(Setting):void
     */
    private function addToRepository(SettingRepository $repository): \Closure
    {
        return function (Setting $setting) use ($repository): void {
            $repository->add($setting);
        };
    }
}
