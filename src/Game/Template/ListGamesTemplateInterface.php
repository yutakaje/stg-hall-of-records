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

namespace Stg\HallOfRecords\Game\Template;

use Psr\Http\Message\ResponseInterface;
use Stg\HallOfRecords\Shared\Application\Query\ListResult;

interface ListGamesTemplateInterface
{
    public function respond(
        ResponseInterface $response,
        ListResult $result
    ): ResponseInterface;
}