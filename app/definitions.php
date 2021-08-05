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

use DI\ContainerBuilder;

return function (ContainerBuilder $builder): void {
    $builder->addDefinitions(require __DIR__ . '/shared.php');
    $builder->addDefinitions(require __DIR__ . '/company.php');
    $builder->addDefinitions(require __DIR__ . '/game.php');
};
