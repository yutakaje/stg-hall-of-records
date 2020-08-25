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

namespace Stg\HallOfRecords\Data\Setting;

use Stg\HallOfRecords\Data\Game\Game;

interface SettingRepositoryInterface
{
    public function filterGlobal(): Settings;

    public function filterByGame(Game $game): Settings;

    public function add(Setting $setting): void;

    public function clear(): void;
}
