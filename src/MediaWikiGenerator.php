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

namespace Stg\HallOfRecords;

use Doctrine\DBAL\Connection;
use Stg\HallOfRecords\Database\InMemoryDatabaseCreator;
use Stg\HallOfRecords\Database\ParsedDataWriter;
use Stg\HallOfRecords\Database\RepositoryFactory;
use Stg\HallOfRecords\Export\MediaWikiExporter;
use Stg\HallOfRecords\Import\ParsedData;
use Stg\HallOfRecords\Import\YamlExtractor;
use Stg\HallOfRecords\Import\YamlParser;

final class MediaWikiGenerator
{
    private InMemoryDatabaseCreator $databaseCreator;
    private RepositoryFactory $repositoryFactory;

    public function __construct(
        InMemoryDatabaseCreator $databaseCreator,
        RepositoryFactory $repositoryFactory
    ) {
        $this->databaseCreator = $databaseCreator;
        $this->repositoryFactory = $repositoryFactory;
    }

    public function generate(string $input, string $locale): string
    {
        $connection = $this->databaseCreator->create();

        $parsedData = $this->parseYaml(
            $this->extractYaml($input),
            $locale
        );

        $this->writeToDatabase($connection, $parsedData);

        return $this->export($connection, $parsedData);
    }

    /**
     * @return array[]
     */
    private function extractYaml(string $input): array
    {
        $extractor = new YamlExtractor();
        return $extractor->extract($input);
    }

    /**
     * @param array<string,mixed>[] $sections
     */
    private function parseYaml(array $sections, string $locale): ParsedData
    {
        $parser = new YamlParser();
        return $parser->parse($sections, $locale);
    }

    private function writeToDatabase(
        Connection $connection,
        ParsedData $parsedData
    ): void {
        $writer = new ParsedDataWriter($connection);
        $writer->write($parsedData);
    }

    private function export(
        Connection $connection,
        ParsedData $parsedData
    ): string {
        $exporter = new MediaWikiExporter(
            $this->repositoryFactory->createGameRepository($connection),
            $this->repositoryFactory->createScoreRepository($connection)
        );
        return $exporter->export();
    }
}
