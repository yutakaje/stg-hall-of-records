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
use Stg\HallOfRecords\Game\Application\Query\ViewGameQueryHandlerInterface;
use Stg\HallOfRecords\Game\Template\ViewGameTemplateInterface;
use Stg\HallOfRecords\Shared\Application\Query\ViewQueryCreator;
use Stg\HallOfRecords\Shared\Infrastructure\Utils\Validator;

final class ViewGameController
{
    private ViewGameQueryHandlerInterface $queryHandler;
    private ViewGameTemplateInterface $template;
    private ViewQueryCreator $queryCreator;
    private LoggerInterface $logger;

    public function __construct(
        ViewGameQueryHandlerInterface $queryHandler,
        ViewGameTemplateInterface $template,
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

        $result = $this->queryHandler->execute(
            $this->queryCreator->create($id, $request)
        );

        $this->logger->info('Game viewed.', [
            'game_id' => $id,
        ]);

        return $this->template->respond($response, $result);
    }
}
