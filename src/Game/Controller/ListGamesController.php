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

namespace Stg\HallOfRecords\Game\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Stg\HallOfRecords\Game\Application\Query\ListGamesQueryHandlerInterface;
use Stg\HallOfRecords\Game\Template\ListGamesTemplateInterface;
use Stg\HallOfRecords\Shared\Application\Query\ListQueryCreator;

final class ListGamesController
{
    private ListGamesQueryHandlerInterface $queryHandler;
    private ListGamesTemplateInterface $template;
    private ListQueryCreator $queryCreator;
    private LoggerInterface $logger;

    public function __construct(
        ListGamesQueryHandlerInterface $queryHandler,
        ListGamesTemplateInterface $template,
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
        $query = $this->queryCreator->create($request);

        $result = $this->queryHandler->execute($query);

        $this->logger->info('Games listed.');

        return $this->template->respond($response, $query, $result);
    }
}
