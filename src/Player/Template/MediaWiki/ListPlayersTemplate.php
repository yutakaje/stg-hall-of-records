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
use Stg\HallOfRecords\Shared\Application\Query\ListQuery;
use Stg\HallOfRecords\Shared\Application\Query\ListResult;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Template\MediaWiki\AbstractTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ListPlayersTemplate extends AbstractTemplate implements
    ListPlayersTemplateInterface
{
    protected function initRenderer(Renderer $renderer): Renderer
    {
        return $renderer->withTemplateFiles(__DIR__ . '/html/list-players');
    }

    public function respond(
        ResponseInterface $response,
        ListQuery $query,
        ListResult $result
    ): ResponseInterface {
        $response->getBody()->write(
            $this->withLocale($query->locale())->createOutput($query, $result)
        );
        return $response;
    }

    private function createOutput(ListQuery $query, ListResult $result): string
    {
        return $this->sharedTemplates()->main(
            $this->renderPlayers($result->resources(), $query),
            $this->routes()->forEachLocale(
                fn ($routes) => $routes->listPlayers()
            ),
            $result->message()
        );
    }

    private function renderPlayers(Resources $players, ListQuery $query): string
    {
        return $this->renderer()->render('main', [
            'players' => $players->map(
                fn (Resource $player) => $this->renderPlayer($player)
            ),
            'filterBox' => $this->sharedTemplates()->filterBox(
                $query->filter(),
                'list-players'
            ),
        ]);
    }

    private function renderPlayer(Resource $player): string
    {
        return $this->renderer()->render('player-entry', [
            'player' => $this->createPlayerVar($player),
            'links' => [
                'player' => $this->routes()->viewPlayer($player->id),
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
