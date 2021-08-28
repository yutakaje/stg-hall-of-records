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
use Stg\HallOfRecords\Shared\Application\Query\ListQuery;
use Stg\HallOfRecords\Shared\Application\Query\ListResult;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Template\MediaWiki\AbstractTemplate;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ListCompaniesTemplate extends AbstractTemplate implements
    ListCompaniesTemplateInterface
{
    protected function initRenderer(Renderer $renderer): Renderer
    {
        return $renderer->withTemplateFiles(__DIR__ . '/html/list-companies');
    }

    public function respond(
        ResponseInterface $response,
        ListQuery $query,
        ListResult $result
    ): ResponseInterface {
        $response->getBody()->write(
            $this->withLocale($query->locale())->createOutput(
                $result->resources()
            )
        );
        return $response;
    }

    private function createOutput(Resources $companies): string
    {
        return $this->sharedTemplates()->main(
            $this->renderCompanies($companies),
            $this->routes()->forEachLocale(
                fn ($routes) => $routes->listCompanies()
            )
        );
    }

    private function renderCompanies(Resources $companies): string
    {
        return $this->renderer()->render('main', [
            'companies' => $companies->map(
                fn (Resource $company) => $this->renderCompany($company)
            ),
        ]);
    }

    private function renderCompany(Resource $company): string
    {
        return $this->renderer()->render('company-entry', [
            'company' => $this->createCompanyVar($company),
            'links' => [
                'company' => $this->routes()->viewCompany($company->id),
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
