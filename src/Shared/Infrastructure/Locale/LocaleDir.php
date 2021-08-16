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

namespace Stg\HallOfRecords\Shared\Infrastructure\Locale;

final class LocaleDir
{
    private string $directory;

    public function __construct(string $directory)
    {
        if (!file_exists($directory)) {
            throw new \InvalidArgumentException(
                "Directory `{$directory}` does not exist"
            );
        }

        $this->directory = $directory;
    }

    public function value(): string
    {
        return $this->directory;
    }
}
