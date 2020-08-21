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

namespace Stg\HallOfRecords\Data;

final class SettingRepository implements SettingRepositoryInterface
{
    /** @var Setting[] */
    private array $settings;

    public function __construct()
    {
        $this->settings = [];
    }

    public function filterGlobal(): Settings
    {
        return new Settings($this->filterSettings(
            fn (Setting $setting) => $setting instanceof GlobalSetting
        ));
    }

    public function filterByGame(Game $game): Settings
    {
        return new Settings($this->filterSettings(
            fn (Setting $setting) => $setting instanceof GameSetting
                && $setting->gameId() === $game->id()
        ));
    }

    public function add(Setting $setting): void
    {
        $this->settings[] = $setting;
    }

    public function clear(): void
    {
        $this->settings = [];
    }

    /**
     * @return Setting[]
     */
    private function filterSettings(\Closure $callback): array
    {
        return array_values(array_filter(
            $this->settings,
            $callback
        ));
    }
}
