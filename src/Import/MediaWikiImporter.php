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

namespace Stg\HallOfRecords\Import;

use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\ScoreRepositoryInterface;
use Stg\HallOfRecords\Import\MediaWiki\YamlExtractor;

final class MediaWikiImporter
{
    private YamlExtractor $extractor;
    private YamlParser $parser;
    private GameRepositoryInterface $games;
    private ScoreRepositoryInterface $scores;

    public function __construct(
        YamlExtractor $extractor,
        YamlParser $parser,
        GameRepositoryInterface $games,
        ScoreRepositoryInterface $scores
    ) {
        $this->extractor = $extractor;
        $this->parser = $parser;
        $this->games = $games;
        $this->scores = $scores;
    }

    public function import(string $input, string $locale): ParsedData
    {
        $parsedData = $this->parse($input, $locale);

        $this->populateRepositories($parsedData);

        return $parsedData;
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

    /**
     * @param array<string,mixed>[] $sections
     */
    private function parseYaml(array $sections, string $locale): ParsedData
    {
        $parser = new YamlParser();
        return $parser->parse($sections, $locale);
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
            $game->getProperty('name') ?? '',
            $game->getProperty('company') ?? ''
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
            $score->getProperty('player') ?? '',
            $score->getProperty('score') ?? '',
            $score->getProperty('ship') ?? '',
            $score->getProperty('mode') ?? '',
            $score->getProperty('weapon') ?? '',
            $score->getProperty('scored-date') ?? '',
            $score->getProperty('source') ?? '',
            $score->getProperty('comments') ?? []
        ));
    }
}
