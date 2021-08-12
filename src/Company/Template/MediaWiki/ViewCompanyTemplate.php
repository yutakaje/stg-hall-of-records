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

namespace Stg\HallOfRecords\Company\Template\MediaWiki;

use Psr\Http\Message\ResponseInterface;
use Stg\HallOfRecords\Company\Template\ViewCompanyTemplateInterface;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Application\Query\ViewResult;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\MediaWiki\BasicTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ViewCompanyTemplate implements ViewCompanyTemplateInterface
{
    private Renderer $renderer;
    private BasicTemplate $wrapper;
    private Routes $routes;

    public function __construct(
        BasicTemplate $wrapper,
        Routes $routes
    ) {
        $this->renderer = Renderer::createWithFiles(
            __DIR__ . '/html/view-company'
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

    private function createOutput(Resource $company, Locale $locale): string
    {
        return $this->wrapper->render($locale, $this->renderCompany(
            $this->renderer->withLocale($locale),
            $company
        ));
    }

    private function renderCompany(
        Renderer $renderer,
        Resource $company
    ): string {
        return $renderer->render('main', [
            'company' => $this->createCompanyVar($company),
            'games' => $this->renderGames($renderer, $company->games),
        ]);
    }

    private function createCompanyVar(Resource $company): \stdClass
    {
        $var = new \stdClass();
        $var->id = $company->id;
        $var->name = $company->name;
        $var->link = $this->routes->viewCompany($company->id);

        return $var;
    }

    private function renderGames(
        Renderer $renderer,
        Resources $games
    ): string {
        return $renderer->render('games-list', [
            'games' => $games->map(
                fn (Resource $game) => $this->renderGame($renderer, $game)
            ),
        ]);
    }

    private function renderGame(
        Renderer $renderer,
        Resource $game
    ): string {
        return $renderer->render('game-entry', [
            'game' => $this->createGameVar($game),
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
}
