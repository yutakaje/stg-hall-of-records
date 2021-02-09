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

namespace Stg\HallOfRecords\Scrap;

use Psr\Http\Message\ResponseInterface;

interface ImageFetcherInterface
{
    public function handles(string $url): bool;

    /**
     * @return ResponseInterface[]
     */
    public function fetch(string $url): array;
}
