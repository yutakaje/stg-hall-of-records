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

abstract class AbstractResult
{
    private ResultMessage $message;

    public function __construct(ResultMessage $message)
    {
        $this->message = $message;
    }

    public function message(): ResultMessage
    {
        return $this->message;
    }
}
