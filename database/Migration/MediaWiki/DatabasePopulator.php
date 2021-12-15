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

namespace Stg\HallOfRecords\Database\Migration\MediaWiki;

use Psr\Log\LoggerInterface;
use Stg\HallOfRecords\Database\Database;
use Stg\HallOfRecords\Data\Game\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Score\ScoreRepositoryInterface;
use Stg\HallOfRecords\Data\Setting\SettingRepositoryInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

final class DatabasePopulator
{
    private Database $database;
    private Locale $defaultLocale;
    private LoggerInterface $logger;
    private SettingRepositoryInterface $settings;
    private GameRepositoryInterface $games;
    private ScoreRepositoryInterface $scores;
    private bool $checkForUnhandledProperties;

    public function __construct(
        Database $database,
        LoggerInterface $logger,
        SettingRepositoryInterface $settings,
        GameRepositoryInterface $games,
        ScoreRepositoryInterface $scores,
        bool $checkForUnhandledProperties = true
    ) {
        $this->database = $database;
        $this->defaultLocale = new Locale('en');
        $this->logger = $logger;
        $this->settings = $settings;
        $this->games = $games;
        $this->scores = $scores;
        $this->checkForUnhandledProperties = $checkForUnhandledProperties;
    }

    public function populate(): void
    {
        $this->logger->info('Import started', [
            'date' => date(DATE_ATOM),
        ]);

        $start = microtime(true);

        $companies = $this->populateCompanies();
        $layoutProperties = $this->populateLayoutProperties();
        $games = $this->populateGames($companies);
        $players = $this->populatePlayers();
        $scores = $this->populateScores($games, $players);

        $this->logger->info('Import finished', [
            'elapsed' => microtime(true) - $start,
        ]);
    }

    private function populateLayoutProperties(): LayoutProperties
    {
        $layoutProperties = new LayoutProperties(
            $this->database,
            $this->logger,
            $this->settings,
            $this->checkForUnhandledProperties
        );
        $layoutProperties->insert();

        return $layoutProperties;
    }

    private function populateCompanies(): Companies
    {
        $companies = new Companies(
            $this->database,
            $this->logger,
            $this->defaultLocale,
            $this->settings,
            $this->checkForUnhandledProperties
        );
        $companies->insert();

        return $companies;
    }

    private function populateGames(Companies $companies): Games
    {
        $games = new Games(
            $this->database,
            $this->logger,
            $companies,
            $this->games,
            $this->checkForUnhandledProperties
        );
        $games->insert();

        return $games;
    }

    private function populatePlayers(): Players
    {
        $players = new Players(
            $this->database,
            $this->logger,
            $this->scores
        );
        $players->insert();

        return $players;
    }

    private function populateScores(Games $games, Players $players): Scores
    {
        $scores = new Scores(
            $this->database,
            $this->logger,
            $games,
            $players,
            $this->scores,
            $this->settings,
            $this->checkForUnhandledProperties
        );
        $scores->insert();

        return $scores;
    }
}
