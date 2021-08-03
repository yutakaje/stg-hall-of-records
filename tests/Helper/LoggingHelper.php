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

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class LoggingHelper
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function init(ContainerInterface $container): self
    {
        return new self(
            $container->get(LoggerInterface::class)
        );
    }

    public static function createLogger(): LoggerInterface
    {
        $logger = new Logger('test');
        $logger->pushHandler(new TestHandler());

        return $logger;
    }

    public function logger(): LoggerInterface
    {
        return $this->logger;
    }
}
