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

use Doctrine\DBAL\Connection;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class ContainerHelper
{
    public static function createContainer(string $rootDir): ContainerInterface
    {
        $appDir = "{$rootDir}/app";

        $builder = new ContainerBuilder();
        $builder->addDefinitions("{$appDir}/definitions.php");

        // Do not use real values for database connection or logger.
        $builder->addDefinitions([
            Connection::class => DatabaseHelper::createConnection(),
            LoggerInterface::class => LoggingHelper::createLogger(),
        ]);

        return $builder->build();
    }
}
