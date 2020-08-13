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

namespace Stg\HallOfRecords\Import;

final class ParsedGlobalProperties
{
    private string $description;

    public function __construct(string $description)
    {
        $this->description = $description;
    }

    public function description(): string
    {
        return $this->description;
    }
}
