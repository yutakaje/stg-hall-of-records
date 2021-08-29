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

namespace Stg\HallOfRecords\Shared\Infrastructure\Http;

final class BaseUri
{
    private string $value;

    public function __construct(string $value = '')
    {
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
