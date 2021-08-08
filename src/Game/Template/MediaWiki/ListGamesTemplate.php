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
use Stg\HallOfRecords\Shared\Application\Query\ListResult;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Template\MediaWiki\BasicTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ListGamesTemplate implements ListGamesTemplateInterface
{
    private Renderer $renderer;
    private BasicTemplate $wrapper;
    private Routes $routes;

    public function __construct(
        BasicTemplate $wrapper,
        Routes $routes
    ) {
        $this->renderer = Renderer::createWithFiles(
            __DIR__ . '/html/list-games'
        );
        $this->wrapper = $wrapper;
        $this->routes = $routes;
    }

    public function respond(
        ResponseInterface $response,
        ListResult $result
    ): ResponseInterface {
        $response->getBody()->write($this->createOutput(
            $result->resources(),
            $result->locale()
        ));
        return $response;
    }

    private function createOutput(Resources $games, string $locale): string
    {
        return $this->wrapper->render($locale, $this->renderGames(
            $this->renderer->withLocale($locale),
            $games
        ));
    }

    private function renderGames(
        Renderer $renderer,
        Resources $games
    ): string {
        return $renderer->render('main', [
            'games' => $games->map(
                fn (Resource $game) => $this->renderGame($renderer, $game)
            ),
        ]);
    }

    private function renderGame(
        Renderer $renderer,
        Resource $game
    ): string {
        return $renderer->render('entry', [
            'game' => $this->createGameVar($game),
        ]);
    }

    private function createGameVar(Resource $game): \stdClass
    {
        $var = new \stdClass();
        $var->id = $game->id;
        $var->name = $game->name;
        $var->link = $this->routes->viewGame($game->id);

        return $var;
    }
}