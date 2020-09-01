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

namespace Stg\HallOfRecords\Export;

use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class TwigFactory
{
    /**
     * @param array<string,string> $templates
     */
    public function create(array $templates): Environment
    {
        return new Environment(
            new ArrayLoader($templates)
        );
    }
}
