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
use Stg\HallOfRecords\Shared\Template\MediaWiki\AbstractTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ListGamesTemplate extends AbstractTemplate implements
    ListGamesTemplateInterface
{
    protected function initRenderer(Renderer $renderer): Renderer
    {
        return $renderer->withTemplateFiles(__DIR__ . '/html/list-games');
    }

    public function respond(
        ResponseInterface $response,
        ListQuery $query,
        ListResult $result
    ): ResponseInterface {
        $response->getBody()->write(
            $this->withLocale($query->locale())->createOutput(
                $result->resources()
            )
        );
        return $response;
    }

    private function createOutput(Resources $games): string
    {
        return $this->sharedTemplates()->main(
            $this->renderGames($games),
            $this->routes()->forEachLocale(
                fn ($routes) => $routes->listGames()
            )
        );
    }

    private function renderGames(Resources $games): string
    {
        return $this->renderer()->render('main', [
            'games' => $games->map(
                fn (Resource $game) => $this->renderGame($game)
            ),
        ]);
    }

    private function renderGame(Resource $game): string
    {
        return $this->renderer()->render('game-entry', [
            'game' => $this->createGameVar($game),
            'links' => [
                'game' => $this->routes()->viewGame($game->id),
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
