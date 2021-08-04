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

use Slim\App;
use Psr\Container\ContainerInterface;
use Stg\HallOfRecords\Company\Controller\ListCompaniesController;
use Stg\HallOfRecords\Company\Controller\ViewCompanyController;
use Stg\HallOfRecords\Company\Infrastructure\Database\ListCompaniesQueryHandler;
use Stg\HallOfRecords\Company\Infrastructure\Database\ViewCompanyQueryHandler;
use Stg\HallOfRecords\Company\Template\MediaWiki\ListCompaniesTemplate;
use Stg\HallOfRecords\Company\Template\MediaWiki\ViewCompanyTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;

return [
    'routes' => DI\add([
        static function (): \Closure {
            return static function (App $app, ContainerInterface $container): void {
                $routes = $container->get(Routes::class);
                $app->get($routes->listCompanies(), 'mediaWiki/listCompanies');
                $app->get($routes->viewCompany(), 'mediaWiki/viewCompany');
            };
        }
    ]),

    'mediaWiki/listCompanies' => DI\autowire(ListCompaniesController::class)
        ->constructorParameter('queryHandler', DI\get(ListCompaniesQueryHandler::class))
        ->constructorParameter('template', DI\get(ListCompaniesTemplate::class)),

    'mediaWiki/viewCompany' => DI\autowire(ViewCompanyController::class)
        ->constructorParameter('queryHandler', DI\get(ViewCompanyQueryHandler::class))
        ->constructorParameter('template', DI\get(ViewCompanyTemplate::class)),

    ListCompaniesQueryHandler::class => DI\autowire(),
    ViewCompanyQueryHandler::class => DI\autowire(),

    ListCompaniesTemplate::class => DI\autowire(),
    ViewCompanyTemplate::class => DI\autowire(),
];
