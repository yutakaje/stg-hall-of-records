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
use Stg\HallOfRecords\Company\Application\Query\ViewCompanyQueryHandlerInterface;
use Stg\HallOfRecords\Company\Template\ViewCompanyTemplateInterface;
use Stg\HallOfRecords\Shared\Application\Query\ViewQueryCreator;
use Stg\HallOfRecords\Shared\Infrastructure\Utils\Validator;

final class ViewCompanyController
{
    private ViewCompanyQueryHandlerInterface $queryHandler;
    private ViewCompanyTemplateInterface $template;
    private ViewQueryCreator $queryCreator;
    private LoggerInterface $logger;

    public function __construct(
        ViewCompanyQueryHandlerInterface $queryHandler,
        ViewCompanyTemplateInterface $template,
        ViewQueryCreator $queryCreator,
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
        $id = Validator::id($args['id'] ?? null);

        $query = $this->queryCreator->create($id, $request);

        $result = $this->queryHandler->execute($query);

        $this->logger->info('Company viewed.', [
            'company_id' => $id,
        ]);

        return $this->template->respond($response, $query, $result);
    }
}
