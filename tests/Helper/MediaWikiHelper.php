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

final class MediaWikiHelper
{
    private FilesystemHelper $filesystem;

    public function __construct(FilesystemHelper $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function loadTemplate(string $context, string $name): string
    {
        $rootDir = $this->filesystem->rootDir();

        return $this->filesystem->loadFile(
            "{$rootDir}/src/{$context}/Template/MediaWiki/html/{$name}.twig"
        );
    }

    public function canonicalizeHtml(string $html): string
    {
        // Strip whitespace between subsequent tags.
        $canonicalized = preg_replace(
            '/>\s*?</',
            ">\n<",
            $html
        );

        if ($canonicalized === null) {
            throw new \UnexpectedValueException('Error canonicalizing html');
        }

        return $canonicalized;
    }
}
