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

namespace Tests\Helper;

final class FilesystemHelper
{
    public function rootDir(): string
    {
        return dirname(dirname(__DIR__));
    }

    public function loadFile(string $filename): string
    {
        $contents = file_get_contents($filename);

        if ($contents === false) {
            throw new \UnexpectedValueException(
                "Unable to load file: `{$filename}`"
            );
        }

        return $contents;
    }
}
