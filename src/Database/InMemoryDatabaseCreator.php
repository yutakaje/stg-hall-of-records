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

namespace Stg\HallOfRecords\Database;

use Doctrine\DBAL\Connection;

final class InMemoryDatabaseCreator
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function create(): void
    {
        $schemaManager = $this->connection->getSchemaManager();
        $schema = $schemaManager->createSchema();

        $games = $schema->createTable('games');
        $games->addColumn('id', 'integer');
        $games->addColumn('name', 'string', ['length' => 100]);
        $games->addColumn('company', 'string', ['length' => 100]);
        //        $games->addColumn('links', 'json');
        //        $games->addColumn('layout', 'json');
        $games->setPrimaryKey(['id']);
        $schemaManager->createTable($games);

        $scores = $schema->createTable('scores');
        $scores->addColumn('id', 'integer');
        $scores->addColumn('game_id', 'integer');
        $scores->addColumn('player', 'string', ['length' => 32]);
        $scores->addColumn('score', 'string', ['length' => 32]);
        //        $scores->addColumn('ship', 'string', ['length' => 32]);
        //        $scores->addColumn('mode', 'string', ['length' => 32]);
        //        $scores->addColumn('weapon', 'string', ['length' => 32]);
        //        $scores->addColumn('scored_date', 'datetime');
        //        $scores->addColumn('source', 'string', ['length' => 64]);
        //        $scores->addColumn('comments', 'json');
        $scores->setPrimaryKey(['id']);
        //$scores->addForeignKeyConstraint($games, ['game_id'], ['id']);
        $schemaManager->createTable($scores);
    }
}
