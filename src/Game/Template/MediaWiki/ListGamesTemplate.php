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

namespace Stg\HallOfRecords\Game\Template\MediaWiki;

use Psr\Http\Message\ResponseInterface;
use Stg\HallOfRecords\Game\Template\ListGamesTemplateInterface;
use Stg\HallOfRecords\Shared\Application\Query\ListQuery;
use Stg\HallOfRecords\Shared\Application\Query\ListResult;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\MediaWiki\SharedTemplates;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ListGamesTemplate implements ListGamesTemplateInterface
{
    private Renderer $renderer;
    private SharedTemplates $sharedTemplates;
    private Routes $routes;

    public function __construct(
        Renderer $renderer,
        SharedTemplates $sharedTemplates,
        Routes $routes
    ) {
        $this->renderer = $renderer->withTemplateFiles(
            __DIR__ . '/html/list-games'
        );
        $this->sharedTemplates = $sharedTemplates;
        $this->routes = $routes;
    }

    public function respond(
        ResponseInterface $response,
        ListQuery $query,
        ListResult $result
    ): ResponseInterface {
        $response->getBody()->write($this->createOutput(
            $result->resources(),
            $query->locale()
        ));
        return $response;
    }

    private function createOutput(Resources $games, Locale $locale): string
    {
        $routes = $this->routes->withLocale($locale);

        return $this->sharedTemplates->withLocale($locale)->main(
            $this->renderGames(
                $this->renderer->withLocale($locale),
                $routes,
                $games
            ),
            $this->routes->forEachLocale(
                fn ($routes) => $routes->listGames()
            )
        );
    }

    private function renderGames(
        Renderer $renderer,
        Routes $routes,
        Resources $games
    ): string {
        return $renderer->render('main', [
            'games' => $games->map(
                fn (Resource $game) => $this->renderGame(
                    $renderer,
                    $routes,
                    $game
                )
            ),
        ]);
    }

    private function renderGame(
        Renderer $renderer,
        Routes $routes,
        Resource $game
    ): string {
        return $renderer->render('game-entry', [
            'game' => $this->createGameVar($game),
            'links' => [
                'game' => $routes->viewGame($game->id),
            ],
        ]);
    }

    private function createGameVar(Resource $game): \stdClass
    {
        $var = new \stdClass();
        $var->id = $game->id;
        $var->name = $game->name;
        $var->numScores = $game->numScores;

        return $var;
    }
}
