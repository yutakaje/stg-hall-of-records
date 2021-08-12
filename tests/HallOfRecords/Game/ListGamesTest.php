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
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Tests\Helper\Data\GameEntry;

class ListGamesTest extends \Tests\TestCase
{
    public function testWithDefaultLocale(): void
    {
        $locale = $this->locale()->default();

        $request = $this->http()->createServerRequest('GET', "/{$locale}/games");

        $this->testWithLocale($request, $locale);
    }

    public function testWithRandomLocale(): void
    {
        $locale = $this->locale()->random();

        $request = $this->http()->createServerRequest('GET', "/{$locale}/games")
            ->withHeader('Accept-Language', $locale->value());

        $this->testWithLocale($request, $locale);
    }

    private function testWithLocale(
        ServerRequestInterface $request,
        Locale $locale
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
    private function createOutput(array $games, Locale $locale): string
    {
        return $this->mediaWiki()->loadBasicTemplate(
            $this->createGamesOutput($games, $locale),
            $locale
        );
    }

    /**
     * @param GameEntry[] $games
     */
    private function createGamesOutput(array $games, Locale $locale): string
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
        Locale $locale
    ): string {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Game', 'list-games/entry'),
            [
                '{{ game.name }}' => $game->name($locale),
                '{{ links.game }}' => "/{$locale}/games/{$game->id()}",
            ]
        );
    }
}
