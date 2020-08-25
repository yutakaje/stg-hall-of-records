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

use Stg\HallOfRecords\Data\Collection;

/**
 * @extends Collection<Setting>
 */
final class Settings extends Collection
{
    /**
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get(string $name, $defaultValue = null)
    {
        foreach ($this->asArray() as $setting) {
            if ($setting->name() === $name) {
                return $setting->value();
            }
        }

        return $defaultValue;
    }
}
