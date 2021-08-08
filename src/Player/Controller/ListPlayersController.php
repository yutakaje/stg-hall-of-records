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

namespace Stg\HallOfRecords\Player\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Stg\HallOfRecords\Player\Application\Query\ListPlayersQueryHandlerInterface;
use Stg\HallOfRecords\Player\Template\ListPlayersTemplateInterface;
use Stg\HallOfRecords\Shared\Application\Query\ListQueryCreator;

final class ListPlayersController
{
    private ListPlayersQueryHandlerInterface $queryHandler;
    private ListPlayersTemplateInterface $template;
    private ListQueryCreator $queryCreator;
    private LoggerInterface $logger;

    public function __construct(
        ListPlayersQueryHandlerInterface $queryHandler,
        ListPlayersTemplateInterface $template,
        ListQueryCreator $queryCreator,
        LoggerInterface $logger
    ) {
        $this->queryHandler = $queryHandler;
        $this->template = $template;
        $this->queryCreator = $queryCreator;
        $this->logger = $logger;
    }

    /**
     * @param QueryParams $args
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args = []
    ): ResponseInterface {
        $result = $this->queryHandler->execute(
            $this->queryCreator->create($request)
        );

        $this->logger->info('Players listed.');

        return $this->template->respond($response, $result);
    }
}
