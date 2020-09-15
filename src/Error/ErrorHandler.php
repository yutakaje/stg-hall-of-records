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

namespace Stg\HallOfRecords\Error;

use Monolog\ErrorHandler as MonologErrorHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;

final class ErrorHandler
{
    private ?LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = null;
    }

    public function registerLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
        MonologErrorHandler::register($logger);
    }

    /**
     * @return string Identifier of the logged message.
     */
    public function logError(\Throwable $error): string
    {
        $identifier = bin2hex(random_bytes(4));
        $timestamp = (new \DateTime())->format('Y-m-d H:i:s.u');

        if ($this->hasLogger()) {
            try {
                $uid = $this->getUidFromLogger();
                if ($uid !== null) {
                    $identifier = $uid;
                }

                $this->logErrorToLogger($error, $identifier, $timestamp);
            } catch (\Throwable $error) {
                $this->logErrorToSystem($error, $identifier, $timestamp);
            }
        } else {
            $this->logErrorToSystem($error, $identifier, $timestamp);
        }

        return $identifier;
    }

    private function logErrorToLogger(
        \Throwable $error,
        string $identifier,
        string $timestamp
    ): void {
        $this->logger()->error($error->getMessage(), [
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'identifier' => $identifier,
            'timestamp' => $timestamp,
            'trace' => $error->getTraceAsString(),
        ]);
    }

    private function logErrorToSystem(
        \Throwable $error,
        string $identifier,
        string $timestamp
    ): void {
        error_log(
            get_class($error) . ': ' . $error->getMessage() . ' {'
            . '"file": "' . $error->getFile() . '", '
            . '"line": "' . $error->getLine() . '", '
            . '"identifier": "' . $identifier . '", '
            . '"timestamp": "' . $timestamp . '"}'
        );
    }

    private function hasLogger(): bool
    {
        return $this->logger !== null;
    }

    private function logger(): LoggerInterface
    {
        if ($this->logger === null) {
            throw new \LogicException('Invalid call to this function');
        }

        return $this->logger;
    }

    private function getUidFromLogger(): ?string
    {
        if ($this->logger() instanceof Logger) {
            foreach ($this->logger()->getProcessors() as $processor) {
                if ($processor instanceof UidProcessor) {
                    return $processor->getUid();
                }
            }
        }

        return null;
    }
}
