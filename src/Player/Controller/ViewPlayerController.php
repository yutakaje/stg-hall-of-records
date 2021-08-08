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
use Stg\HallOfRecords\Player\Application\Query\ViewPlayerQueryHandlerInterface;
use Stg\HallOfRecords\Player\Template\ViewPlayerTemplateInterface;
use Stg\HallOfRecords\Shared\Application\Query\ViewQueryCreator;
use Stg\HallOfRecords\Shared\Infrastructure\Utils\Validator;

final class ViewPlayerController
{
    private ViewPlayerQueryHandlerInterface $queryHandler;
    private ViewPlayerTemplateInterface $template;
    private ViewQueryCreator $queryCreator;
    private LoggerInterface $logger;

    public function __construct(
        ViewPlayerQueryHandlerInterface $queryHandler,
        ViewPlayerTemplateInterface $template,
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

        $this->logger->info('Player viewed.', [
            'player_id' => $id,
        ]);

        return $this->template->respond($response, $result);
    }
}
