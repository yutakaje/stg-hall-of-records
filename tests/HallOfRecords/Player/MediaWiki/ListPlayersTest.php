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
use Tests\Helper\Data\PlayerEntry;

class ListPlayersTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        // Index represents expected sort order.
        $players = [
            1 => $this->data()->createPlayer('Player-2'),
            0 => $this->data()->createPlayer('Player-1'),
            2 => $this->data()->createPlayer('プレイヤー-3'),
        ];

        foreach ($players as $player) {
            $this->addScores($player);
        }

        $this->testWithLocale($players, $this->locale()->get('en'));
    }

    public function testWithJaLocale(): void
    {
        // Index represents expected sort order.
        $players = [
            1 => $this->data()->createPlayer('Player-2'),
            0 => $this->data()->createPlayer('Player-1'),
            2 => $this->data()->createPlayer('プレイヤー-3'),
        ];

        foreach ($players as $player) {
            $this->addScores($player);
        }

        $this->testWithLocale($players, $this->locale()->get('ja'));
    }

    /**
     * @param PlayerEntry[] $players
     */
    private function testWithLocale(
        array $players,
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
        $numScores = random_int(1, 5);
        for ($i = 0; $i < $numScores; ++$i) {
            $player->addScore(
                $this->data()->createScore(
                    $this->data()->createGame(
                        $this->data()->createCompany("company{$i}"),
                        "game{$i}"
                    ),
                    $player,
                    "player{$i}",
                    (string)random_int(1000, 99999)
                )
            );
        }
    }

    /**
     * @param PlayerEntry[] $players
     */
    private function insertPlayers(array $players): void
    {
        foreach ($players as $player) {
            $this->data()->insertPlayer($player);
            $this->data()->insertScores($player->scores());
        }
    }

    /**
     * @param PlayerEntry[] $players
     */
    private function createOutput(array $players, Locale $locale): string
    {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadBasicTemplate(
                    $this->createPlayersOutput($players, $locale),
                    $locale,
                    '/{locale}/players'
                )
            )
        );
    }

    /**
     * @param PlayerEntry[] $players
     */
    private function createPlayersOutput(array $players, Locale $locale): string
    {
        ksort($players);

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Player', 'list-players/main'),
            [
                '{{ entry|raw }}' => implode(PHP_EOL, array_map(
                    fn (PlayerEntry $player) => $this->createPlayerOutput(
                        $player,
                        $locale
                    ),
                    $players
                )),
            ]
        );
    }

    private function createPlayerOutput(
        PlayerEntry $player,
        Locale $locale
    ): string {
        $numScores = sizeof($player->scores());

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
