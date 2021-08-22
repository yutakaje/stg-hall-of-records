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

namespace Stg\HallOfRecords\Shared\Infrastructure\Log;

use Monolog\ErrorHandler as MonologErrorHandler;
use Psr\Log\LoggerInterface;

final class ErrorHandler
{
    public static function init(): void
    {
        // Convert all errors into exceptions.
        set_error_handler(function (
            int $errno,
            string $errstr,
            string $errfile,
            int $errline
        ): bool {
            // Errors may not be thrown if their type is ignored. This
            // is important for the error control operator @ to work.
            if (($errno & error_reporting()) !== $errno) {
                return false;
            }
            throw new \Error("{$errstr} at {$errfile} line {$errline}", $errno);
        });
    }

    public static function register(LoggerInterface $logger): void
    {
        $handler = new MonologErrorHandler($logger);
        $handler->registerExceptionHandler();
        $handler->registerFatalHandler();
    }

    /**
     * @return string Identifier of the logged message.
     */
    public static function logError(\Throwable $error): string
    {
        $identifier = md5(random_bytes(64));

        $message = get_class($error) . ": {$error->getMessage()} {"
            . '"file": "' . $error->getFile() . '", '
            . '"line": "' . $error->getLine() . '", '
            . '"identifier": "' . $identifier . '", '
            . '"timestamp": "' . (new \DateTime())->format('Y-m-d H:i:s.u') . '"}';
        error_log($message);

        return $identifier;
    }
}
