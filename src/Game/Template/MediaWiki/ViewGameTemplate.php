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
use Stg\HallOfRecords\Shared\Application\Query\ViewResult;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\MediaWiki\BasicTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ViewGameTemplate implements ViewGameTemplateInterface
{
    private Renderer $renderer;
    private BasicTemplate $wrapper;
    private Routes $routes;

    public function __construct(
        BasicTemplate $wrapper,
        Routes $routes
    ) {
        $this->renderer = Renderer::createWithFiles(
            __DIR__ . '/html/view-game'
        );
        $this->wrapper = $wrapper;
        $this->routes = $routes;
    }

    public function respond(
        ResponseInterface $response,
        ViewResult $result
    ): ResponseInterface {
        $response->getBody()->write($this->createOutput(
            $result->resource(),
            $result->locale()
        ));
        return $response;
    }

    private function createOutput(Resource $game, Locale $locale): string
    {
        return $this->wrapper->render($locale, $this->renderGame(
            $this->renderer->withLocale($locale),
            $game
        ));
    }

    private function renderGame(
        Renderer $renderer,
        Resource $game
    ): string {
        return $renderer->render('main', [
            'game' => $this->createGameVar($game),
            'company' => $this->createCompanyVar($game->company),
            'scores' => $this->renderScores($renderer, $game->scores),
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

    private function createCompanyVar(Resource $company): \stdClass
    {
        $var = new \stdClass();
        $var->id = $company->id;
        $var->name = $company->name;
        $var->link = $this->routes->viewCompany($company->id);

        return $var;
    }

    private function renderScores(
        Renderer $renderer,
        Resources $scores
    ): string {
        return $renderer->render('scores-list', [
            'scores' => $scores->map(
                fn (Resource $score) => $this->renderScore($renderer, $score)
            ),
        ]);
    }

    private function renderScore(
        Renderer $renderer,
        Resource $score
    ): string {
        return $renderer->render('score-entry', [
            'score' => $this->createScoreVar($score),
            'player' => $this->createPlayerVar($score),
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
        $var->link = $this->routes->viewPlayer($score->playerId);

        return $var;
    }
}
