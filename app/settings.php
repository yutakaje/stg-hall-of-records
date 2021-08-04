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

use Monolog\Logger;

return [
    'compileContainer' => true,
    'debugMode' => false,
    'logger' => [
        'name' => 'stg-hall-of-records',
        'path' => dirname(__DIR__) . '/logs/app.log',
        'level' => Logger::DEBUG,
        'numFiles' => 30,
    ],
    'database' => [
        'driver' => 'pdo_sqlite',
        'path' => __DIR__ . '/stg-hor.db',
    ],
];
