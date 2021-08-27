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

final class ListResult extends AbstractResult
{
    private Resources $resources;

    public function __construct(Resources $resources)
    {
        $this->resources = $resources;
    }

    public function resources(): Resources
    {
        return $this->resources;
    }
}
