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

namespace Tests\HallOfRecords\Import;

use Stg\HallOfRecords\Import\MediaWikiImporter;
use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\ScoreRepositoryInterface;
use Stg\HallOfRecords\Import\ParsedGame;
use Stg\HallOfRecords\Import\ParsedScore;
use Stg\HallOfRecords\Import\YamlExtractor;
use Stg\HallOfRecords\Import\YamlParser;

class MediaWikiImporterTest extends \Tests\TestCase
{
    public function testImport(): void
    {
        $locale = 'en';

        $expectedData = (new YamlParser())->parse(
            (new YamlExtractor())->extract(
                $this->loadFile(__DIR__ . '/wiki-input')
            ),
            $locale
        );

        $storage = new \stdClass();
        $storage->games = [];
        $storage->scores = [];

        $gameRepository = $this->createMock(GameRepositoryInterface::class);
        $gameRepository->method('add')
            ->will(self::returnCallback(
                function (Game $game) use ($storage): void {
                    $storage->games[] = $game;
                }
            ));

        $scoreRepository = $this->createMock(ScoreRepositoryInterface::class);
        $scoreRepository->method('add')
            ->will(self::returnCallback(
                function (Score $score) use ($storage): void {
                    $storage->scores[] = $score;
                }
            ));

        $importer = new MediaWikiImporter(
            new YamlExtractor(),
            new YamlParser(),
            $gameRepository,
            $scoreRepository
        );

        self::assertEquals($expectedData, $importer->import(
            $this->loadFile(__DIR__ . '/wiki-input'),
            $locale
        ));

        // Assert that repositories have been populated.
        self::assertEquals(
            array_map(
                fn (ParsedGame $parsedGame) => $this->createGame([
                    'id' => $parsedGame->id(),
                    'name' => $parsedGame->name(),
                    'company' => $parsedGame->company(),
                ]),
                $expectedData->games()
            ),
            $storage->games
        );

        self::assertEquals(
            array_reduce(
                $expectedData->games(),
                fn (array $scores, ParsedGame $parsedGame) => array_merge(
                    $scores,
                    array_map(
                        fn (ParsedScore $parsedScore) => $this->createScore([
                            'id' => $parsedScore->id(),
                            'gameId' => $parsedGame->id(),
                            'player' => $parsedScore->player(),
                            'score' => $parsedScore->score(),
                            'ship' => $parsedScore->ship(),
                            'mode' => $parsedScore->mode(),
                            'weapon' => $parsedScore->weapon(),
                            'scoredDate' => $parsedScore->scoredDate(),
                            'source' => $parsedScore->source(),
                            'comments' => $parsedScore->comments(),
                        ]),
                        $parsedGame->scores()
                    )
                ),
                []
            ),
            $storage->scores
        );
    }
}
