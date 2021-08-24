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
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\MediaWiki\BasicTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ListPlayersTemplate implements ListPlayersTemplateInterface
{
    private Renderer $renderer;
    private BasicTemplate $wrapper;
    private Routes $routes;

    public function __construct(
        Renderer $renderer,
        BasicTemplate $wrapper,
        Routes $routes
    ) {
        $this->renderer = $renderer->withTemplateFiles(
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

    private function createOutput(Resources $players, Locale $locale): string
    {
        $routes = $this->routes->withLocale($locale);

        return $this->wrapper->render(
            $locale,
            $this->renderPlayers(
                $this->renderer->withLocale($locale),
                $routes,
                $players
            ),
            $this->routes->forEachLocale(
                fn ($routes) => $routes->listPlayers()
            )
        );
    }

    private function renderPlayers(
        Renderer $renderer,
        Routes $routes,
        Resources $players
    ): string {
        return $renderer->render('main', [
            'players' => $players->map(
                fn (Resource $player) => $this->renderPlayer(
                    $renderer,
                    $routes,
                    $player
                )
            ),
        ]);
    }

    private function renderPlayer(
        Renderer $renderer,
        Routes $routes,
        Resource $player
    ): string {
        return $renderer->render('player-entry', [
            'player' => $this->createPlayerVar($player),
            'links' => [
                'player' => $routes->viewPlayer($player->id),
            ],
        ]);
    }

    private function createPlayerVar(Resource $player): \stdClass
    {
        $var = new \stdClass();
        $var->id = $player->id;
        $var->name = $player->name;
        $var->numScores = $player->numScores;

        return $var;
    }
}
