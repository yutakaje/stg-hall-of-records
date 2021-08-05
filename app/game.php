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
use Stg\HallOfRecords\Game\Controller\ListGamesController;
use Stg\HallOfRecords\Game\Controller\ViewGameController;
use Stg\HallOfRecords\Game\Infrastructure\Database\ListGamesQueryHandler;
use Stg\HallOfRecords\Game\Infrastructure\Database\ViewGameQueryHandler;
use Stg\HallOfRecords\Game\Template\MediaWiki\ListGamesTemplate;
use Stg\HallOfRecords\Game\Template\MediaWiki\ViewGameTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;

return [
    'routes' => DI\add([
        static function (): \Closure {
            return static function (App $app, ContainerInterface $container): void {
                $routes = $container->get(Routes::class);
                $app->get($routes->listGames(), 'mediaWiki/listGames');
                $app->get($routes->viewGame(), 'mediaWiki/viewGame');
            };
        }
    ]),

    'mediaWiki/listGames' => DI\autowire(ListGamesController::class)
        ->constructorParameter('queryHandler', DI\get(ListGamesQueryHandler::class))
        ->constructorParameter('template', DI\get(ListGamesTemplate::class)),

    'mediaWiki/viewGame' => DI\autowire(ViewGameController::class)
        ->constructorParameter('queryHandler', DI\get(ViewGameQueryHandler::class))
        ->constructorParameter('template', DI\get(ViewGameTemplate::class)),

    ListGamesQueryHandler::class => DI\autowire(),
    ViewGameQueryHandler::class => DI\autowire(),

    ListGamesTemplate::class => DI\autowire(),
    ViewGameTemplate::class => DI\autowire(),
];
