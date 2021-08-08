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
use Stg\HallOfRecords\Player\Controller\ListPlayersController;
use Stg\HallOfRecords\Player\Controller\ViewPlayerController;
use Stg\HallOfRecords\Player\Infrastructure\Database\ListPlayersQueryHandler;
use Stg\HallOfRecords\Player\Infrastructure\Database\ViewPlayerQueryHandler;
use Stg\HallOfRecords\Player\Template\MediaWiki\ListPlayersTemplate;
use Stg\HallOfRecords\Player\Template\MediaWiki\ViewPlayerTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;

return [
    'routes' => DI\add([
        static function (): \Closure {
            return static function (App $app, ContainerInterface $container): void {
                $routes = $container->get(Routes::class);
                $app->get($routes->listPlayers(), 'mediaWiki/listPlayers');
                $app->get($routes->viewPlayer(), 'mediaWiki/viewPlayer');
            };
        }
    ]),

    'mediaWiki/listPlayers' => DI\autowire(ListPlayersController::class)
        ->constructorParameter('queryHandler', DI\get(ListPlayersQueryHandler::class))
        ->constructorParameter('template', DI\get(ListPlayersTemplate::class)),

    'mediaWiki/viewPlayer' => DI\autowire(ViewPlayerController::class)
        ->constructorParameter('queryHandler', DI\get(ViewPlayerQueryHandler::class))
        ->constructorParameter('template', DI\get(ViewPlayerTemplate::class)),

    ListPlayersQueryHandler::class => DI\autowire(),
    ViewPlayerQueryHandler::class => DI\autowire(),

    ListPlayersTemplate::class => DI\autowire(),
    ViewPlayerTemplate::class => DI\autowire(),
];
