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
use Psr\Http\Message\ServerRequestInterface;
use Tests\Helper\Data\PlayerEntry;

class ListPlayersTest extends \Tests\TestCase
{
    public function testWithDefaultLocale(): void
    {
        $request = $this->http()->createServerRequest('GET', '/players');

        $this->testWithLocale($request, $this->locale()->default());
    }

    public function testWithRandomLocale(): void
    {
        $locale = $this->locale()->random();

        $request = $this->http()->createServerRequest('GET', '/players')
            ->withHeader('Accept-Language', $locale);

        $this->testWithLocale($request, $locale);
    }

    private function testWithLocale(
        ServerRequestInterface $request,
        string $locale
    ): void {
        $players = $this->createPlayers();

        $this->insertPlayers($players);

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

    /**
     * @return PlayerEntry[]
     */
    private function createPlayers(): array
    {
        return [
            $this->data()->createPlayer('WTN'),
            $this->data()->createPlayer('KTL-NAL'),
            $this->data()->createPlayer('こいずみ'),
        ];
    }

    /**
     * @param PlayerEntry[] $players
     */
    private function insertPlayers(array $players): void
    {
        foreach ($players as $player) {
            $this->data()->insertPlayer($player);
        }
    }

    /**
     * @param PlayerEntry[] $players
     */
    private function createOutput(array $players, string $locale): string
    {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Shared', 'basic'),
            [
                '{{content|raw}}' => $this->createPlayersOutput($players, $locale),
            ]
        );
    }

    /**
     * @param PlayerEntry[] $players
     */
    private function createPlayersOutput(array $players, string $locale): string
    {
        return $this->mediaWiki()->removePlaceholders(
            $this->data()->replace(
                $this->mediaWiki()->loadTemplate('Player', 'list-players/main'),
                [
                    '{{ entry|raw }}' => implode(PHP_EOL, array_map(
                        fn (PlayerEntry $player) => $this->createPlayerOutput(
                            $player,
                            $locale
                        ),
                        [
                            $players[1],
                            $players[0],
                            $players[2],
                        ]
                    )),
                ]
            )
        );
    }

    private function createPlayerOutput(
        PlayerEntry $player,
        string $locale
    ): string {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Player', 'list-players/player-entry'),
            [
                '{{ player.link }}' => "/players/{$player->id()}",
                '{{ player.name }}' => $player->name(),
            ]
        );
    }
}
