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

namespace Tests\HallOfRecords\Player\MediaWiki;

use Fig\Http\Message\StatusCodeInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Tests\Helper\Data\PlayerEntries;
use Tests\Helper\Data\PlayerEntry;
use Tests\Helper\Data\ScoreEntries;

class ListPlayersTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        $players = new PlayerEntries([
            $this->data()->createPlayer('player1'),
            $this->data()->createPlayer('player2'),
            $this->data()->createPlayer('player3'),
        ]);

        foreach ($players->entries() as $i => $player) {
            $this->addScores($player, $i + 1);
        }

        $this->insertPlayers($players);

        $this->executeTest(
            'list-players.output.en',
            $this->locale()->get('en')
        );
    }

    public function testWithJaLocale(): void
    {
        $players = new PlayerEntries([
            $this->data()->createPlayer('プレイヤー1'),
            $this->data()->createPlayer('プレイヤー2'),
            $this->data()->createPlayer('プレイヤー3'),
        ]);

        foreach ($players->entries() as $i => $player) {
            $this->addScores($player, $i + 1);
        }

        $this->insertPlayers($players);

        $this->executeTest(
            'list-players.output.ja',
            $this->locale()->get('ja')
        );
    }

    public function testFiltering(): void
    {
        $players = new PlayerEntries([
            $this->data()->createPlayer('player1'),
            $this->data()->createPlayer('player2', ['alias1']),
            $this->data()->createPlayer('player3'),
            $this->data()->createPlayer('プレイヤー1'),
            $this->data()->createPlayer('プレイヤー2'),
            $this->data()->createPlayer('プレイヤー3'),
        ]);

        foreach ($players->entries() as $i => $player) {
            $this->addScores($player, $i);
        }

        $this->insertPlayers($players);

        $this->executeTest(
            'list-players.output.en.filtered1',
            $this->locale()->get('en'),
            'name like 1'
        );
        $this->executeTest(
            'list-players.output.ja.filtered2',
            $this->locale()->get('ja'),
            'name like 2'
        );
        $this->executeTest(
            'list-players.output.en.filtered3',
            $this->locale()->get('en'),
            'name like 3'
        );
    }

    private function executeTest(
        string $expectedOutputFile,
        Locale $locale,
        string $filterValue = ''
    ): void {
        $request = $this->http()->createServerRequest(
            'GET',
            "/{$locale}/players"
        );

        if ($filterValue !== '') {
            $request = $request->withQueryParams([
                'q' => $filterValue,
            ]);
        }

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->createOutput(
                $this->filesystem()->loadFile(
                    __DIR__ . "/list-players/{$expectedOutputFile}"
                ),
                $locale
            ),
            (string)$response->getBody()
        );
    }

    private function createOutput(string $content, Locale $locale): string
    {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadMainTemplate(
                    $content,
                    $locale,
                    '/{locale}/players'
                )
            )
        );
    }

    private function addScores(PlayerEntry $player, int $numScores): void
    {
        // Adding scores ensures that the count functions work as expected.
        // The actual score properties are not important here.
        $scores = array_map(
            fn (int $i) => $this->data()->createScore(
                $this->data()->createGame(
                    $this->data()->createCompany("company{$i}"),
                    "game{$i}"
                ),
                $player,
                "player{$i}",
                (string)random_int(1000, 99999)
            ),
            range(1, $numScores)
        );

        $player->setScores(new ScoreEntries($scores));
    }

    private function insertPlayers(PlayerEntries $players): void
    {
        foreach ($players->entries() as $player) {
            $this->data()->insertPlayer($player);
            $this->data()->insertScores($player->scores()->entries());
        }
    }
}
