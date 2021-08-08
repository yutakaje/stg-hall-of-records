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

class ViewGameTest extends \Tests\TestCase
{
    public function testWithDefaultLocale(): void
    {
        $request = $this->http()->createServerRequest('GET', '/games/{id}');

        $this->testWithLocale($request, $this->locale()->default());
    }

    public function testWithRandomLocale(): void
    {
        $locale = $this->locale()->random();

        $request = $this->http()->createServerRequest('GET', '/games/{id}')
            ->withHeader('Accept-Language', $locale);

        $this->testWithLocale($request, $locale);
    }

    private function testWithLocale(
        ServerRequestInterface $request,
        string $locale
    ): void {
        $game = $this->createGameEntry();

        $this->insertGame($game);

        $request = $this->http()->replaceInUriPath(
            $request,
            '{id}',
            (string)$game->id()
        );

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->mediaWiki()->canonicalizeHtml(
                $this->createOutput($game, $locale)
            ),
            $this->mediaWiki()->canonicalizeHtml(
                (string)$response->getBody()
            )
        );
    }

    private function createGameEntry(): GameEntry
    {
        return $this->data()->createGame(
            'ketsui',
            $this->data()->createCompany('cave')
        );
    }

    private function insertGame(GameEntry $game): void
    {
        $this->data()->insertGame($game);
    }

    private function createOutput(GameEntry $game, string $locale): string
    {
        return str_replace(
            '{{content|raw}}',
            $this->createGameOutput($game, $locale),
            $this->mediaWiki()->loadTemplate('Shared', 'basic')
        );
    }

    private function createGameOutput(
        GameEntry $game,
        string $locale
    ): string {
        $company = $game->company();

        return str_replace(
            [
                '{{ game.name }}',
                '{{ company.link }}',
                '{{ company.name }}',
            ],
            [
                $game->name($locale),
                "/companies/{$company->id()}",
                $company->name($locale),
            ],
            $this->mediaWiki()->loadTemplate('Game', 'view-game/main')
        );
    }
}
