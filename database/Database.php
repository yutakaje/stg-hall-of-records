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
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;

final class Database
{
    private Connection $connection;
    private CompaniesTable $companies;

    public function __construct(
        Connection $connection,
        Locales $locales
    ) {
        $this->connection = $connection;
        $this->companies = new CompaniesTable($this->connection, $locales);
    }

    public function createObjects(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        $schema = $schemaManager->createSchema();

        $companies = $this->companies->createObjects($schemaManager, $schema);
    }

    public function companies(): CompaniesTable
    {
        return $this->companies;
    }
}