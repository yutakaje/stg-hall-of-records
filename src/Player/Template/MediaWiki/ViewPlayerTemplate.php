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
use Stg\HallOfRecords\Player\Template\ViewPlayerTemplateInterface;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Application\Query\ViewQuery;
use Stg\HallOfRecords\Shared\Application\Query\ViewResult;
use Stg\HallOfRecords\Shared\Template\MediaWiki\AbstractTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ViewPlayerTemplate extends AbstractTemplate implements
    ViewPlayerTemplateInterface
{
    protected function initRenderer(Renderer $renderer): Renderer
    {
        return $renderer->withTemplateFiles(__DIR__ . '/html/view-player');
    }

    public function respond(
        ResponseInterface $response,
        ViewQuery $query,
        ViewResult $result
    ): ResponseInterface {
        $response->getBody()->write(
            $this->withLocale($query->locale())->createOutput($result)
        );
        return $response;
    }

    private function createOutput(ViewResult $result): string
    {
        return $this->sharedTemplates()->main(
            $this->renderPlayer($result->resource()),
            $this->routes()->forEachLocale(
                fn ($routes) => $routes->viewPlayer($result->resource()->id)
            ),
            $result->message()
        );
    }

    private function renderPlayer(Resource $player): string
    {
        return $this->renderer()->render('main', [
            'player' => $this->createPlayerVar($player),
            'scores' => $this->renderScores($player->scores),
        ]);
    }

    private function createPlayerVar(Resource $player): \stdClass
    {
        $var = new \stdClass();
        $var->id = $player->id;
        $var->name = $player->name;
        $var->aliases = $player->aliases;

        return $var;
    }

    private function renderScores(Resources $scores): string
    {
        return $this->renderer()->render('scores', [
            'scores' => $scores->map(
                fn (Resource $score) => $this->createScoreVar($score)
            ),
        ]);
    }

    private function createScoreVar(Resource $score): \stdClass
    {
        $var = new \stdClass();
        $var->id = $score->id;
        $var->game = $this->createGameVar($score);
        $var->playerName = $score->playerName;
        $var->value = $score->scoreValue;

        return $var;
    }

    private function createGameVar(Resource $score): \stdClass
    {
        $var = new \stdClass();
        $var->id = $score->gameId;
        $var->name = $score->gameName;
        $var->link = $this->routes()->viewGame($score->gameId);

        return $var;
    }
}
