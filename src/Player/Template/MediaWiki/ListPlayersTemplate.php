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

namespace Stg\HallOfRecords\Player\Template\MediaWiki;

use Psr\Http\Message\ResponseInterface;
use Stg\HallOfRecords\Player\Template\ListPlayersTemplateInterface;
use Stg\HallOfRecords\Shared\Application\Query\ListResult;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Template\MediaWiki\BasicTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ListPlayersTemplate implements ListPlayersTemplateInterface
{
    private Renderer $renderer;
    private BasicTemplate $wrapper;
    private Routes $routes;

    public function __construct(
        BasicTemplate $wrapper,
        Routes $routes
    ) {
        $this->renderer = Renderer::createWithFiles(
            __DIR__ . '/html/list-players'
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

    private function createOutput(Resources $players, string $locale): string
    {
        return $this->wrapper->render($locale, $this->renderPlayers(
            $this->renderer->withLocale($locale),
            $players
        ));
    }

    private function renderPlayers(
        Renderer $renderer,
        Resources $players
    ): string {
        return $renderer->render('main', [
            'players' => $players->map(
                fn (Resource $player) => $this->renderPlayer($renderer, $player)
            ),
        ]);
    }

    private function renderPlayer(
        Renderer $renderer,
        Resource $player
    ): string {
        return $renderer->render('player-entry', [
            'player' => $this->createPlayerVar($player),
        ]);
    }

    private function createPlayerVar(Resource $player): \stdClass
    {
        $var = new \stdClass();
        $var->id = $player->id;
        $var->name = $player->name;
        $var->link = $this->routes->viewPlayer($player->id);

        return $var;
    }
}