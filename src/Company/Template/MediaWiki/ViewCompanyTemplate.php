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
use Stg\HallOfRecords\Shared\Application\Query\ViewQuery;
use Stg\HallOfRecords\Shared\Application\Query\ViewResult;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\MediaWiki\SharedTemplates;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ViewCompanyTemplate implements ViewCompanyTemplateInterface
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
            __DIR__ . '/html/view-company'
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

    private function createOutput(Resource $company, Locale $locale): string
    {
        $routes = $this->routes->withLocale($locale);

        return $this->sharedTemplates->withLocale($locale)->main(
            $this->renderCompany(
                $this->renderer->withLocale($locale),
                $routes->withLocale($locale),
                $company
            ),
            $this->routes->forEachLocale(
                fn ($routes) => $routes->viewCompany($company->id)
            )
        );
    }

    private function renderCompany(
        Renderer $renderer,
        Routes $routes,
        Resource $company
    ): string {
        return $renderer->render('main', [
            'company' => $this->createCompanyVar($company),
            'games' => $this->renderGames($renderer, $routes, $company->games),
        ]);
    }

    private function createCompanyVar(Resource $company): \stdClass
    {
        $var = new \stdClass();
        $var->id = $company->id;
        $var->name = $company->name;

        return $var;
    }

    private function renderGames(
        Renderer $renderer,
        Routes $routes,
        Resources $games
    ): string {
        return $renderer->render('games-list', [
            'games' => $games->map(
                fn (Resource $game) => $this->renderGame(
                    $renderer,
                    $routes,
                    $game
                )
            ),
        ]);
    }

    private function renderGame(
        Renderer $renderer,
        Routes $routes,
        Resource $game
    ): string {
        return $renderer->render('game-entry', [
            'game' => $this->createGameVar($game),
            'links' => [
                'game' => $routes->viewGame($game->id),
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
}
