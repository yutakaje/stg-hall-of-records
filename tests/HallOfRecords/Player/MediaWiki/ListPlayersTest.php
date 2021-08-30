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

        foreach ($players->entries() as $player) {
            $this->addScores($player);
        }

        $this->insertPlayers($players);

        $this->executeTest($players, $this->locale()->get('en'));
    }

    public function testWithJaLocale(): void
    {
        $players = new PlayerEntries([
            $this->data()->createPlayer('プレイヤー1'),
            $this->data()->createPlayer('プレイヤー2'),
            $this->data()->createPlayer('プレイヤー3'),
        ]);

        foreach ($players->entries() as $player) {
            $this->addScores($player);
        }

        $this->insertPlayers($players);

        $this->executeTest($players, $this->locale()->get('ja'));
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

        foreach ($players->entries() as $player) {
            $this->addScores($player);
        }

        $this->insertPlayers($players);

        $this->executeTest(
            new PlayerEntries([
                $players->entryAt(0),
                $players->entryAt(1),
                $players->entryAt(3),
            ]),
            $this->locale()->get('en'),
            'name like 1'
        );
        $this->executeTest(
            new PlayerEntries([
                $players->entryAt(1),
                $players->entryAt(4),
            ]),
            $this->locale()->get('ja'),
            'name like 2'
        );
        $this->executeTest(
            new PlayerEntries([
                $players->entryAt(2),
                $players->entryAt(5),
            ]),
            $this->locale()->get('en'),
            'name like 3'
        );
    }

    private function executeTest(
        PlayerEntries $players,
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
            $this->mediaWiki()->canonicalizeHtml(
                $this->createOutput($players, $locale, $filterValue)
            ),
            $this->mediaWiki()->canonicalizeHtml(
                (string)$response->getBody()
            )
        );
    }

    private function addScores(PlayerEntry $player): void
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
            range(1, random_int(1, 5))
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

    private function createOutput(
        PlayerEntries $players,
        Locale $locale,
        string $filterValue
    ): string {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadMainTemplate(
                    $this->createPlayersOutput($players, $locale, $filterValue),
                    $locale,
                    '/{locale}/players'
                )
            )
        );
    }

    private function createPlayersOutput(
        PlayerEntries $players,
        Locale $locale,
        string $filterValue
    ): string {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Player', 'list-players/main'),
            [
                '{{ players|length }}' => $players->numEntries(),
                '{{ entry|raw }}' => implode(PHP_EOL, array_map(
                    fn (PlayerEntry $player) => $this->createPlayerOutput(
                        $player,
                        $locale
                    ),
                    $players->sorted()
                )),
                '{{ filterBox|raw }}' => $this->mediaWiki()->loadFilterBoxTemplate(
                    $filterValue,
                    'list-players'
                ),
            ]
        );
    }

    private function createPlayerOutput(
        PlayerEntry $player,
        Locale $locale
    ): string {
        $numScores = $player->scores()->numEntries();

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Player', 'list-players/player-entry'),
            [
                '{{ player.name }}' => $player->name(),
                '{{ player.numScores }}' => $numScores,
                "{'%count%': player.numScores}" => "{'%count%': {$numScores}}",
                '{{ links.player }}' => "/{$locale}/players/{$player->id()}",
            ]
        );
    }
}
