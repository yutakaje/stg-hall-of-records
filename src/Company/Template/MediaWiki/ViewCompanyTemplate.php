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
use Stg\HallOfRecords\Shared\Template\MediaWiki\AbstractTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ViewCompanyTemplate extends AbstractTemplate implements
    ViewCompanyTemplateInterface
{
    protected function initRenderer(Renderer $renderer): Renderer
    {
        return $renderer->withTemplateFiles(__DIR__ . '/html/view-company');
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

    private function createOutput(Resource $company): string
    {
        return $this->sharedTemplates()->main(
            $this->renderCompany($company),
            $this->routes()->forEachLocale(
                fn ($routes) => $routes->viewCompany($company->id)
            )
        );
    }

    private function renderCompany(Resource $company): string
    {
        return $this->renderer()->render('main', [
            'company' => $this->createCompanyVar($company),
            'games' => $this->renderGames($company->games),
        ]);
    }

    private function createCompanyVar(Resource $company): \stdClass
    {
        $var = new \stdClass();
        $var->id = $company->id;
        $var->name = $company->name;

        return $var;
    }

    private function renderGames(Resources $games): string
    {
        return $this->renderer()->render('games-list', [
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

        return $var;
    }
}
