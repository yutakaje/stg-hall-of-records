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

namespace Tests\HallOfRecords\Game;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Helper\Data\GameEntry;

class ListGamesTest extends \Tests\TestCase
{
    public function testWithDefaultLocale(): void
    {
        $request = $this->http()->createServerRequest('GET', '/games');

        $this->testWithLocale($request, $this->locale()->default());
    }

    public function testWithRandomLocale(): void
    {
        $locale = $this->locale()->random();

        $request = $this->http()->createServerRequest('GET', '/games')
            ->withHeader('Accept-Language', $locale);

        $this->testWithLocale($request, $locale);
    }

    private function testWithLocale(
        ServerRequestInterface $request,
        string $locale
    ): void {
        $games = $this->createGames();

        $this->insertGames($games);

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->mediaWiki()->canonicalizeHtml(
                $this->createOutput($games, $locale)
            ),
            $this->mediaWiki()->canonicalizeHtml(
                (string)$response->getBody()
            )
        );
    }

    /**
     * @return GameEntry[]
     */
    private function createGames(): array
    {
        $companies = [
            $this->data()->createCompany('konami'),
            $this->data()->createCompany('cave'),
            $this->data()->createCompany('raizing'),
        ];

        // Index represents expected sort order.
        return [
            1 => $this->data()->createGame(
                $companies[1],
                'ケツイ〜絆地獄たち〜',
                'けついきずなじごくたち'
            ),
            0 => $this->data()->createGame(
                $companies[1],
                'エスプレイド',
                'えすぷれいど'
            ),
            3 => $this->data()->createGame(
                $companies[2],
                'バトルガレッガ',
                'ばとるがれっが'
            ),
            2 => $this->data()->createGame(
                $companies[0],
                '出たな!ツインビー',
                'でたな!ついんびー',
            ),
        ];
    }

    /**
     * @param GameEntry[] $games
     */
    private function insertGames(array $games): void
    {
        foreach ($games as $game) {
            $this->data()->insertGame($game);
        }
    }

    /**
     * @param GameEntry[] $games
     */
    private function createOutput(array $games, string $locale): string
    {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Shared', 'basic'),
            [
                '{{content|raw}}' => $this->createGamesOutput($games, $locale),
            ]
        );
    }

    /**
     * @param GameEntry[] $games
     */
    private function createGamesOutput(array $games, string $locale): string
    {
        ksort($games);

        return $this->mediaWiki()->removePlaceholders(
            $this->data()->replace(
                $this->mediaWiki()->loadTemplate('Game', 'list-games/main'),
                [
                    '{{ entry|raw }}' => implode(PHP_EOL, array_map(
                        fn (GameEntry $game) => $this->createGameOutput(
                            $game,
                            $locale
                        ),
                        $games
                    )),
                ]
            )
        );
    }

    private function createGameOutput(
        GameEntry $game,
        string $locale
    ): string {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Game', 'list-games/entry'),
            [
                '{{ game.link }}' => "/games/{$game->id()}",
                '{{ game.name }}' => $game->name($locale),
            ]
        );
    }
}
