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
use Stg\HallOfRecords\Company\Template\ListCompaniesTemplateInterface;
use Stg\HallOfRecords\Shared\Application\Query\ListResult;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\MediaWiki\BasicTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ListCompaniesTemplate implements ListCompaniesTemplateInterface
{
    private Renderer $renderer;
    private BasicTemplate $wrapper;
    private Routes $routes;

    public function __construct(
        Renderer $renderer,
        BasicTemplate $wrapper,
        Routes $routes
    ) {
        $this->renderer = $renderer->withTemplateFiles(
            __DIR__ . '/html/list-companies'
        );
        $this->wrapper = $wrapper;
        $this->routes = $routes;
    }

    public function respond(
        ResponseInterface $response,
        ListResult $result
    ): ResponseInterface {
        $response->getBody()->write($this->createOutput(
            $result->resources(),
            $result->locale()
        ));
        return $response;
    }

    private function createOutput(Resources $companies, Locale $locale): string
    {
        $routes = $this->routes->withLocale($locale);

        return $this->wrapper->render($locale, $this->renderCompanies(
            $this->renderer->withLocale($locale),
            $routes,
            $companies
        ), ['self' => $routes->listCompanies()]);
    }

    private function renderCompanies(
        Renderer $renderer,
        Routes $routes,
        Resources $companies
    ): string {
        return $renderer->render('main', [
            'companies' => $companies->map(
                fn (Resource $company) => $this->renderCompany(
                    $renderer,
                    $routes,
                    $company
                )
            ),
        ]);
    }

    private function renderCompany(
        Renderer $renderer,
        Routes $routes,
        Resource $company
    ): string {
        return $renderer->render('company-entry', [
            'company' => $this->createCompanyVar($company),
            'links' => [
                'company' => $routes->viewCompany($company->id),
            ],
        ]);
    }

    private function createCompanyVar(Resource $company): \stdClass
    {
        $var = new \stdClass();
        $var->id = $company->id;
        $var->name = $company->name;
        $var->numGames = $company->numGames;

        return $var;
    }
}
