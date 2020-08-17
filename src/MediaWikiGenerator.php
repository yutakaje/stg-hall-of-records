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

use Stg\HallOfRecords\Data\GameRepositoryInterface;
use Stg\HallOfRecords\Data\ScoreRepositoryInterface;
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
        return $this->export(
            $this->parse($input, $locale)
        );
    }

    private function parse(string $input, string $locale): ParsedData
    {
        return $this->parseYaml(
            $this->extractYaml($input),
            $locale
        );
    }

    private function export(ParsedData $parsedData): string
    {
        $connection = $this->databaseCreator->create();
        $games = $this->repositoryFactory->createGameRepository($connection);
        $scores = $this->repositoryFactory->createScoreRepository($connection);

        $this->writeToDatabase($games, $scores, $parsedData);

        return $this->exportToWiki($games, $scores, $parsedData);
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
        GameRepositoryInterface $games,
        ScoreRepositoryInterface $scores,
        ParsedData $parsedData
    ): void {
        $writer = new ParsedDataWriter($games, $scores);
        $writer->write($parsedData);
    }

    private function exportToWiki(
        GameRepositoryInterface $games,
        ScoreRepositoryInterface $scores,
        ParsedData $parsedData
    ): string {
        $exporter = new MediaWikiExporter(
            $games,
            $scores,
            $parsedData->globalProperties()->templates()
        );
        return $exporter->export($parsedData->layouts());
    }
}
