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

        $this->testWithLocale($players, $this->locale()->get('en'));
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

        $this->testWithLocale($players, $this->locale()->get('ja'));
    }

    private function testWithLocale(
        PlayerEntries $players,
        Locale $locale
    ): void {
        $this->insertPlayers($players);

        $request = $this->http()->createServerRequest(
            'GET',
            "/{$locale}/players"
        );

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->mediaWiki()->canonicalizeHtml(
                $this->createOutput($players, $locale)
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
        Locale $locale
    ): string {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadMainTemplate(
                    $this->createPlayersOutput($players, $locale),
                    $locale,
                    '/{locale}/players'
                )
            )
        );
    }

    private function createPlayersOutput(
        PlayerEntries $players,
        Locale $locale
    ): string {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Player', 'list-players/main'),
            [
                '{{ entry|raw }}' => implode(PHP_EOL, array_map(
                    fn (PlayerEntry $player) => $this->createPlayerOutput(
                        $player,
                        $locale
                    ),
                    $players->sorted()
                )),
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
