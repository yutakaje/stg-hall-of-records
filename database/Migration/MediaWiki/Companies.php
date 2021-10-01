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
use Stg\HallOfRecords\Database\Definition\CompanyRecord;
use Stg\HallOfRecords\Data\Setting\SettingRepositoryInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

final class Companies
{
    private Database $database;
    private LoggerInterface $logger;
    private Locale $defaultLocale;
    private SettingRepositoryInterface $sourceSettings;
    private bool $checkForUnhandledProperties;
    /** @var CompanyRecord[] */
    private array $records;

    public function __construct(
        Database $database,
        LoggerInterface $logger,
        Locale $defaultLocale,
        SettingRepositoryInterface $sourceSettings,
        bool $checkForUnhandledProperties
    ) {
        $this->database = $database;
        $this->logger = $logger;
        $this->defaultLocale = $defaultLocale;
        $this->sourceSettings = $sourceSettings;
        $this->checkForUnhandledProperties = $checkForUnhandledProperties;
        $this->records = [];
    }

    public function insert(): void
    {
        $this->logger->info('Importing companies');

        $start = microtime(true);

        $this->records = $this->createRecords();

        $this->database->companies()->insertRecords($this->records);

        $this->logger->info('Companies imported', [
            'total' => sizeof($this->records),
            'elapsed' => microtime(true) - $start,
        ]);
    }

    /**
     * @return CompanyRecord[]
     */
    private function createRecords(): array
    {
        $translations = array_filter(
            $this->sourceSettings->filterGlobal()->get('translations'),
            fn (array $entry) => $entry['property'] === 'company'
        );

        return array_map(
            fn (array $company) => $this->createRecord($company),
            $translations
        );
    }

    /**
     * @param array<string,string> $company
     */
    private function createRecord(array $company): CompanyRecord
    {
        $this->logger->debug('Creating company', [
            'name' => $company['value'] ?? null,
        ]);

        $properties = new Properties($company);

        $names = [
            'en' => $properties->consume('value'),
            'ja' => $properties->consume('value-jp'),
        ];

        $properties->remove('property');

        if ($this->checkForUnhandledProperties) {
            $properties->assertEmpty();
        }

        // We do not have transliterated values for Japanese company names.
        return $this->database->companies()->createRecord(
            $names,
            [
                'en' => mb_strtolower($names['en']),
                'ja' => mb_strtolower($names['ja']),
            ]
        );
    }

    public function find(string $name): CompanyRecord
    {
        foreach ($this->records as $record) {
            if ($record->name($this->defaultLocale) === $name) {
                return $record;
            }
        }

        throw new \InvalidArgumentException(
            "Company named `{$name}` does not exist."
        );
    }
}
