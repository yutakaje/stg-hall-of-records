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

namespace Stg\HallOfRecords\Company\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Stg\HallOfRecords\Company\Application\Query\ListCompaniesQueryHandlerInterface;
use Stg\HallOfRecords\Company\Template\ListCompaniesTemplateInterface;
use Stg\HallOfRecords\Shared\Application\Query\ListQueryCreator;

final class ListCompaniesController
{
    private ListCompaniesQueryHandlerInterface $queryHandler;
    private ListCompaniesTemplateInterface $template;
    private ListQueryCreator $queryCreator;
    private LoggerInterface $logger;

    public function __construct(
        ListCompaniesQueryHandlerInterface $queryHandler,
        ListCompaniesTemplateInterface $template,
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

        $this->logger->info('Companies listed.');

        return $this->template->respond($response, $result);
    }
}
