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

namespace Tests\Helper;

use Stg\HallOfRecords\Database\Database;
use Tests\Helper\Data\CompanyEntry;
use Tests\Helper\Data\GameEntry;
use Tests\Helper\Data\PlayerEntry;
use Tests\Helper\Data\ScoreEntry;

/**
 * @phpstan-import-type Aliases from PlayerEntry
 */
final class DataHelper
{
    private Database $database;
    private LocaleHelper $localizer;

    public function __construct(
        Database $database,
        LocaleHelper $localizer
    ) {
        $this->database = $database;
        $this->localizer = $localizer;
    }

    public function createCompany(
        string $name,
        string $translitName = ''
    ): CompanyEntry {
        if ($translitName === '') {
            $translitName = "{$name}-translit";
        }

        return new CompanyEntry(
            $this->localizer->localize($name),
            $this->localizer->localize($translitName)
        );
    }

    public function insertCompany(CompanyEntry $company): void
    {
        $company->insert(
            $this->database->companies()
        );
    }

    public function createGame(
        CompanyEntry $company,
        string $name,
        string $translitName = ''
    ): GameEntry {
        if ($translitName === '') {
            $translitName = "{$name}-translit";
        }

        return new GameEntry(
            $company,
            $this->localizer->localize($name),
            $this->localizer->localize($translitName)
        );
    }

    public function insertGame(GameEntry $game): void
    {
        $this->insertCompany($game->company());
        $game->insert(
            $this->database->games()
        );
    }

    /**
     * @param GameEntry[] $games
     */
    public function insertGames(array $games): void
    {
        foreach ($games as $game) {
            $this->insertGame($game);
        }
    }

    /**
     * @param Aliases $aliases
     */
    public function createPlayer(
        string $name,
        array $aliases = []
    ): PlayerEntry {
        return new PlayerEntry(
            $name,
            $aliases
        );
    }

    public function insertPlayer(PlayerEntry $player): void
    {
        $player->insert(
            $this->database->players()
        );
    }

    public function createScore(
        GameEntry $game,
        ?PlayerEntry $player,
        string $playerName,
        string $scoreValue
    ): ScoreEntry {
        return new ScoreEntry(
            $game,
            $player,
            $playerName,
            $scoreValue
        );
    }

    public function insertScore(ScoreEntry $score): void
    {
        $this->insertGame($score->game());
        if ($score->player() !== null) {
            $this->insertPlayer($score->player());
        }
        $score->insert(
            $this->database->scores()
        );
    }

    /**
     * @param ScoreEntry[] $scores
     */
    public function insertScores(array $scores): void
    {
        foreach ($scores as $score) {
            $this->insertScore($score);
        }
    }

    /**
     * @param array<string,string|int> $replacements
     */
    public function replace(string $value, array $replacements): string
    {
        foreach ($replacements as $search => $replace) {
            $value = str_replace($search, (string)$replace, $value);
        }

        return $value;
    }
}
