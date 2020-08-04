<?php

declare(strict_types=1);

/*
 * This file is part of the stg/hall-of-records package.
 *
 * (c) YTK <yutakaje@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stg\HallOfRecords\Parser;

use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\Games;
use Stg\HallOfRecords\Data\GlobalProperties;

final class YamlParser
{
    /**
     * @var array<string,mixed>
     */
    private array $globalProperties;

    /**
     * @var array<string,mixed>[]
     */
    private array $games;

    /**
     * @param array<string,mixed>[] $sections
     */
    public function __construct(array $sections)
    {
        $this->globalProperties = [];
        $this->games = [];

        $this->import($sections);
    }

    public function parseGlobalProperties(): GlobalProperties
    {
        return new GlobalProperties($this->globalProperties);
    }

    /**
     * @return Games
     */
    public function parseGames(): Games
    {
        return new Games(array_map(
            fn (array $section) => new Game($section),
            $this->games
        ));
    }

    /**
     * @param array<string,mixed>[] $sections
     */
    private function import(array $sections): void
    {
        if ($sections == null) {
            return;
        }

        if (
            !isset($sections[0])
            || !isset($sections[0]['name'])
            || $sections[0]['name'] !== 'global'
        ) {
            throw new \InvalidArgumentException(
                'First section must contain the global properties.'
                . ' Its property `name` must be set to `global`.'
            );
        }

        $this->globalProperties = $sections[0];
        $this->games = array_slice($sections, 1);
    }
}
