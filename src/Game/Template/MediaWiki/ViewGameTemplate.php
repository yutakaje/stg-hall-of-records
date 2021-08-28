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
            $this->withLocale($query->locale())->createOutput(
                $result->resource()
            )
        );
        return $response;
    }

    private function createOutput(Resource $game): string
    {
        return $this->sharedTemplates()->main(
            $this->renderGame($game),
            $this->routes()->forEachLocale(
                fn ($routes) => $routes->viewGame($game->id)
            )
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
        $var->scoreValue = $score->scoreValue;

        return $var;
    }

    private function createPlayerVar(Resource $score): \stdClass
    {
        $var = new \stdClass();
        $var->id = $score->playerId;
        $var->name = $score->playerName;

        return $var;
    }
}
