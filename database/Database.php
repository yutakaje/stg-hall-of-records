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

namespace Stg\HallOfRecords\Database;

use Doctrine\DBAL\Connection;
use Stg\HallOfRecords\Database\Definition\CompaniesTable;
use Stg\HallOfRecords\Database\Definition\GamesTable;
use Stg\HallOfRecords\Database\Definition\LayoutPropertiesTable;
use Stg\HallOfRecords\Database\Definition\PlayersTable;
use Stg\HallOfRecords\Database\Definition\ScoresTable;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;

final class Database
{
    private Connection $connection;
    private CompaniesTable $companies;
    private GamesTable $games;
    private LayoutPropertiesTable $layoutProperties;
    private PlayersTable $players;
    private ScoresTable $scores;

    public function __construct(
        Connection $connection,
        Locales $locales
    ) {
        $this->connection = $connection;
        $this->companies = new CompaniesTable($this->connection, $locales);
        $this->games = new GamesTable($this->connection, $locales);
        $this->layoutProperties = new LayoutPropertiesTable($this->connection, $locales);
        $this->players = new PlayersTable($this->connection, $locales);
        $this->scores = new ScoresTable($this->connection, $locales);
    }

    public function createObjects(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        $schema = $schemaManager->createSchema();

        $companies = $this->companies->createObjects($schemaManager, $schema);

        $games = $this->games->createObjects($schemaManager, $schema, $companies);

        $players = $this->players->createObjects($schemaManager, $schema);

        $this->scores->createObjects($schemaManager, $schema, $games, $players);

        $this->layoutProperties->createObjects($schemaManager, $schema, $games);
    }

    public function companies(): CompaniesTable
    {
        return $this->companies;
    }

    public function games(): GamesTable
    {
        return $this->games;
    }

    public function layoutProperties(): LayoutPropertiesTable
    {
        return $this->layoutProperties;
    }

    public function players(): PlayersTable
    {
        return $this->players;
    }

    public function scores(): ScoresTable
    {
        return $this->scores;
    }
}
