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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Psr\Container\ContainerInterface;
use Stg\HallOfRecords\Database\Database;

final class DatabaseHelper
{
    private Database $database;
    private bool $isInitialized;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->isInitialized = false;
    }

    public static function init(ContainerInterface $container): self
    {
        return new self(
            $container->get(Database::class)
        );
    }

    public function database(): Database
    {
        if (!$this->isInitialized) {
            $this->database->createObjects();
            $this->isInitialized = true;
        }

        return $this->database;
    }

    public static function createConnection(): Connection
    {
        // Use database in memory.
        return DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
    }

    public function fakeConnection(): Connection
    {
        // Use database in memory.
        return DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
    }
}
