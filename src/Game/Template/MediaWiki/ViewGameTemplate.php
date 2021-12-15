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
use Stg\HallOfRecords\Game\Template\ViewGameTemplateInterface;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Application\Query\ViewQuery;
use Stg\HallOfRecords\Shared\Application\Query\ViewResult;
use Stg\HallOfRecords\Shared\Template\MediaWiki\AbstractTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ViewGameTemplate extends AbstractTemplate implements
    ViewGameTemplateInterface
{
    protected function initRenderer(Renderer $renderer): Renderer
    {
        return $renderer->withTemplateFiles(__DIR__ . '/html/view-game');
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
            $this->renderGame($result->resource()),
            $this->routes()->forEachLocale(
                fn ($routes) => $routes->viewGame($result->resource()->id)
            ),
            $result->message()
        );
    }

    private function renderGame(Resource $game): string
    {
        return $this->renderer()->render('main', [
            'game' => $this->createGameVar($game),
            'company' => $this->createCompanyVar($game->company),
            'scores' => $this->renderScores($game->scores),
            'links' => [
                'company' => $this->routes()->viewCompany($game->company->id),
            ],
        ]);
    }

    private function createGameVar(Resource $game): \stdClass
    {
        $var = new \stdClass();
        $var->id = $game->id;
        $var->name = $game->name;
        $var->description = $game->description;
        $var->links = $game->links->map(
            fn (Resource $link) => $this->createGameLinkVar($link)
        );

        return $var;
    }

    private function createCompanyVar(Resource $company): \stdClass
    {
        $var = new \stdClass();
        $var->id = $company->id;
        $var->name = $company->name;

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
        $var->player = $this->createPlayerVar($score);
        $var->value = $score->scoreValue;
        $var->realValue = $score->realScoreValue;
        $var->sources = $score->sources->map(
            fn (Resource $source) => $this->createSourceVar($source)
        );

        return $var;
    }

    private function createPlayerVar(Resource $score): \stdClass
    {
        $var = new \stdClass();
        $var->id = $score->playerId;
        $var->name = $score->playerName;

        if ($score->playerId !== null) {
            $var->link = $this->routes()->viewPlayer($score->playerId);
        }

        return $var;
    }

    private function createGameLinkVar(Resource $link): \stdClass
    {
        $var = new \stdClass();
        $var->url = $link->url;
        $var->title = $link->title;

        return $var;
    }

    private function createSourceVar(Resource $source): \stdClass
    {
        $var = new \stdClass();
        $var->name = $source->name;
        $var->date = $source->date;
        $var->url = $source->url;

        return $var;
    }
}
