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

final class GameSetting extends Setting
{
    private int $gameId;

    /**
     * @param mixed $value
     */
    public function __construct(
        int $gameId,
        string $name,
        $value
    ) {
        parent::__construct($name, $value);
        $this->gameId = $gameId;
    }

    public function gameId(): int
    {
        return $this->gameId;
    }

    /**
     * @return array<string,mixed>
     */
    public function additionalProperties(): array
    {
        return [
            'gameId' => $this->gameId,
        ];
    }
}
