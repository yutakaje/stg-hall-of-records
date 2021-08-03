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

namespace Tests\Helper;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;

final class AppHelper
{
    public static function createApp(ContainerInterface $container): App
    {
        // Instantiate the app
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        // Add middleware and routes to the app.
        foreach ($container->get('middleware') as $addMiddleware) {
            $addMiddleware($app, $container);
        }
        foreach ($container->get('routes') as $addRoutes) {
            $addRoutes($app, $container);
        }

        $app->addRoutingMiddleware();

        $errorMiddleware = $app->addErrorMiddleware(
            true,
            true,
            true,
            $container->get(LoggerInterface::class)
        );

        return $app;
    }
}
