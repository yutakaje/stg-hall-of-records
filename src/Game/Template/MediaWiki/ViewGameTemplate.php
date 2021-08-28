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
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\MediaWiki\SharedTemplates;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ViewGameTemplate implements ViewGameTemplateInterface
{
    private Renderer $renderer;
    private SharedTemplates $sharedTemplates;
    private Routes $routes;

    public function __construct(
        Renderer $renderer,
        SharedTemplates $sharedTemplates,
        Routes $routes
    ) {
        $this->renderer = $renderer->withTemplateFiles(
            __DIR__ . '/html/view-game'
        );
        $this->sharedTemplates = $sharedTemplates;
        $this->routes = $routes;
    }

    public function respond(
        ResponseInterface $response,
        ViewQuery $query,
        ViewResult $result
    ): ResponseInterface {
        $response->getBody()->write($this->createOutput(
            $result->resource(),
            $query->locale()
        ));
        return $response;
    }

    private function createOutput(Resource $game, Locale $locale): string
    {
        $routes = $this->routes->withLocale($locale);

        return $this->sharedTemplates->withLocale($locale)->main(
            $this->renderGame(
                $this->renderer->withLocale($locale),
                $routes,
                $game
            ),
            $this->routes->forEachLocale(
                fn ($routes) => $routes->viewGame($game->id)
            )
        );
    }

    private function renderGame(
        Renderer $renderer,
        Routes $routes,
        Resource $game
    ): string {
        return $renderer->render('main', [
            'game' => $this->createGameVar($game),
            'company' => $this->createCompanyVar($game->company),
            'scores' => $this->renderScores($renderer, $routes, $game->scores),
            'links' => [
                'company' => $routes->viewCompany($game->company->id),
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

    private function renderScores(
        Renderer $renderer,
        Routes $routes,
        Resources $scores
    ): string {
        return $renderer->render('scores-list', [
            'scores' => $scores->map(
                fn (Resource $score) => $this->renderScore(
                    $renderer,
                    $routes,
                    $score
                )
            ),
        ]);
    }

    private function renderScore(
        Renderer $renderer,
        Routes $routes,
        Resource $score
    ): string {
        return $renderer->render('score-entry', [
            'score' => $this->createScoreVar($score),
            'player' => $this->createPlayerVar($score),
            'links' => [
                'player' => $routes->viewPlayer($score->playerId),
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
