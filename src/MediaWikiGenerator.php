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

use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\ScoreRepositoryInterface;
use Stg\HallOfRecords\Export\MediaWikiExporter;
use Stg\HallOfRecords\Import\ParsedData;
use Stg\HallOfRecords\Import\ParsedGame;
use Stg\HallOfRecords\Import\ParsedScore;
use Stg\HallOfRecords\Import\YamlExtractor;
use Stg\HallOfRecords\Import\YamlParser;

final class MediaWikiGenerator
{
    private GameRepositoryInterface $games;
    private ScoreRepositoryInterface $scores;

    public function __construct(
        GameRepositoryInterface $games,
        ScoreRepositoryInterface $scores
    ) {
        $this->games = $games;
        $this->scores = $scores;
    }

    public function generate(string $input, string $locale): string
    {
        $parsedData = $this->parse($input, $locale);

        $this->populateRepositories($parsedData);

        return $this->export($parsedData);
    }

    private function parse(string $input, string $locale): ParsedData
    {
        return $this->parseYaml(
            $this->extractYaml($input),
            $locale
        );
    }

    /**
     * @return array[]
     */
    private function extractYaml(string $input): array
    {
        $extractor = new YamlExtractor();
        return $extractor->extract($input);
    }

    private function populateRepositories(ParsedData $parsedData): void
    {
        foreach ($parsedData->games() as $game) {
            $this->addGameToRepository($game);
        }
    }

    private function addGameToRepository(ParsedGame $game): void
    {
        $this->games->add(new Game(
            $game->id(),
            $game->name(),
            $game->company()
        ));

        foreach ($game->scores() as $score) {
            $this->addScoreToRepository($game->id(), $score);
        }
    }

    private function addScoreToRepository(int $gameId, ParsedScore $score): void
    {
        $this->scores->add(new Score(
            $score->id(),
            $gameId,
            $score->player(),
            $score->score(),
            $score->ship(),
            $score->mode(),
            $score->weapon(),
            $score->scoredDate(),
            $score->source(),
            $score->comments()
        ));
    }

    /**
     * @param array<string,mixed>[] $sections
     */
    private function parseYaml(array $sections, string $locale): ParsedData
    {
        $parser = new YamlParser();
        return $parser->parse($sections, $locale);
    }

    private function export(ParsedData $parsedData): string
    {
        $exporter = new MediaWikiExporter(
            $this->games,
            $this->scores,
            $parsedData->globalProperties()->templates()
        );
        return $exporter->export($parsedData->layouts());
    }
}
