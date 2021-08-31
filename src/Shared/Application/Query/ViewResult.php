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

namespace Stg\HallOfRecords\Shared\Application\Query;

use Stg\HallOfRecords\Shared\Application\ResultMessage;

final class ViewResult extends AbstractResult
{
    private Resource $resource;

    public function __construct(
        Resource $resource,
        ?ResultMessage $message = null
    ) {
        parent::__construct($message ?? ResultMessage::none());
        $this->resource = $resource;
    }

    public function resource(): Resource
    {
        return $this->resource;
    }
}
