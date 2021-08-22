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

// Read environment variables from file if available.
$env = [];

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $contents = parse_ini_file($envFile, false, INI_SCANNER_TYPED);
    if (is_array($contents)) {
        $env = $contents;
    }
}

return [
    'compileContainer' => $env['COMPILE_CONTAINER'] ?? true,
    'debugMode' => $env['DEBUG_MODE'] ?? false,
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
    'http' => [
        'baseUri' => $env['HTTP_BASE_URI'] ?? '',
    ],
];
