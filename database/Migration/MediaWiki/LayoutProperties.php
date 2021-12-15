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
use Stg\HallOfRecords\Database\Definition\LayoutPropertyRecord;
use Stg\HallOfRecords\Data\Setting\SettingRepositoryInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

final class LayoutProperties
{
    private Database $database;
    private LoggerInterface $logger;
    private SettingRepositoryInterface $sourceSettings;
    private bool $checkForUnhandledProperties;
    /** @var LayoutPropertyRecord[] */
    private array $records;

    public function __construct(
        Database $database,
        LoggerInterface $logger,
        SettingRepositoryInterface $sourceSettings,
        bool $checkForUnhandledProperties
    ) {
        $this->database = $database;
        $this->logger = $logger;
        $this->sourceSettings = $sourceSettings;
        $this->checkForUnhandledProperties = $checkForUnhandledProperties;
        $this->records = [];
    }

    public function insert(): void
    {
        $this->logger->info('Importing layout properties');

        $start = microtime(true);

        $this->records = $this->createRecords();

        $this->database->layoutProperties()->insertRecords($this->records);

        $this->logger->info('Layout properties imported', [
            'total' => sizeof($this->records),
            'elapsed' => microtime(true) - $start,
        ]);
    }

    /**
     * @return LayoutPropertyRecord[]
     */
    private function createRecords(): array
    {
        $globalLayout = new Properties(
            $this->sourceSettings->filterGlobal()->get('layout', [])
        );

        $records = [
            $this->createCategoriesRecord($globalLayout),
            $this->createColumnOrderRecord($globalLayout),
        ];

        /* @TODO Handle remaining properties */
        $globalLayout->remove(
            'columns',
            'templates',
            'sort',
        );

        if ($this->checkForUnhandledProperties) {
            $globalLayout->assertEmpty();
        }

        return $records;
    }

    private function createCategoriesRecord(
        Properties $globalLayout
    ): LayoutPropertyRecord {
        $this->logger->debug('Creating global property', [
            'name' => 'categories',
        ]);

        $categories = $globalLayout->consume('group')['scores'];

        return $this->database->layoutProperties()->createRecord(
            null,
            'categories',
            $categories
        );
    }

    private function createColumnOrderRecord(
        Properties $globalLayout
    ): LayoutPropertyRecord {
        $this->logger->debug('Creating global property', [
            'name' => 'column-order',
        ]);

        $categories = $globalLayout->consume('column-order');

        return $this->database->layoutProperties()->createRecord(
            null,
            'column-order',
            $categories
        );
    }

    public function find(string $name, ?int $gameId = null): LayoutPropertyRecord
    {
        foreach ($this->records as $record) {
            if ($record->name() === $name && $record->gameId() === $gameId) {
                return $record;
            }
        }

        if ($gameId !== null) {
            throw new \InvalidArgumentException(
                "Layout property named `{$name}` does not"
                . " exist for game id `{$gameId}`."
            );
        } else {
            throw new \InvalidArgumentException(
                "Global layout property with name `{$name}` does not exist."
            );
        }
    }
}
