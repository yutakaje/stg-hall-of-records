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
            'gameLinks' => $this->renderGameLinks($game->links),
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
        return $this->renderer()->render('scores-list', [
            'scores' => $scores->map(
                fn (Resource $score) => $this->renderScore($score)
            ),
        ]);
    }

    private function renderScore(Resource $score): string
    {
        return $this->renderer()->render('score-entry', [
            'score' => $this->createScoreVar($score),
            'player' => $this->createPlayerVar($score),
            'scoreValue' => $this->renderScoreValue($score),
            'sources' => $this->renderSources($score->sources),
            'links' => [
                'player' => $this->routes()->viewPlayer($score->playerId),
            ],
        ]);
    }

    private function createScoreVar(Resource $score): \stdClass
    {
        $var = new \stdClass();
        $var->id = $score->id;
        $var->playerName = $score->playerName;

        return $var;
    }

    private function createPlayerVar(Resource $score): \stdClass
    {
        $var = new \stdClass();
        $var->id = $score->playerId;
        $var->name = $score->playerName;

        return $var;
    }

    private function renderGameLinks(Resources $links): string
    {
        return $this->renderer()->render('links-list', [
            'links' => $links->map(
                fn (Resource $link) => $this->renderGameLink($link)
            ),
        ]);
    }

    private function renderGameLink(Resource $link): string
    {
        return $this->renderer()->render('link-entry', [
            'link' => $this->createGameLinkVar($link),
        ]);
    }

    private function createGameLinkVar(Resource $link): \stdClass
    {
        $var = new \stdClass();
        $var->url = $link->url;
        $var->title = $link->title;

        return $var;
    }

    private function renderScoreValue(Resource $source): string
    {
        return $this->renderer()->render('score-value', [
            'score' => $this->createScoreValueVar($source),
        ]);
    }

    private function createScoreValueVar(Resource $score): \stdClass
    {
        $var = new \stdClass();
        $var->value = $score->scoreValue;
        $var->realValue = $score->realScoreValue;

        return $var;
    }

    private function renderSources(Resources $sources): string
    {
        return $this->renderer()->render('sources-list', [
            'sources' => $sources->map(
                fn (Resource $source) => $this->renderSource($source)
            ),
        ]);
    }

    private function renderSource(Resource $source): string
    {
        return $this->renderer()->render('source-entry', [
            'source' => $this->createSourceVar($source),
        ]);
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
