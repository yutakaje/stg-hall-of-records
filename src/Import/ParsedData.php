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

namespace Stg\HallOfRecords\Import;

final class ParsedData
{
    private ParsedGlobalProperties $globalProperties;
    /** @var ParsedGame[] */
    private array $games;

    /**
     * @param ParsedGame[] $games
     */
    public function __construct(
        ParsedGlobalProperties $globalProperties,
        array $games
    ) {
        $this->globalProperties = $globalProperties;
        $this->games = $games;
    }

    public function globalProperties(): ParsedGlobalProperties
    {
        return $this->globalProperties;
    }

    /**
     * @return ParsedGame[]
     */
    public function games(): array
    {
        return $this->games;
    }

    /**
     * @return array<int,ParsedLayout>
     */
    public function layouts(): array
    {
        $layouts = [];

        foreach ($this->games as $game) {
            $layouts[$game->id()] = $game->layout();
        }

        return $layouts;
    }
}
