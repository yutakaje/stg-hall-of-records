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

use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

final class MediaWikiHelper
{
    private FilesystemHelper $filesystem;
    private DataHelper $data;

    public function __construct(
        FilesystemHelper $filesystem,
        DataHelper $data
    ) {
        $this->filesystem = $filesystem;
        $this->data = $data;
    }

    public function loadTemplate(string $context, string $name): string
    {
        $rootDir = $this->filesystem->rootDir();

        return $this->filesystem->loadFile(
            "{$rootDir}/src/{$context}/Template/MediaWiki/html/{$name}.twig"
        );
    }

    public function loadBasicTemplate(string $content, Locale $locale): string
    {
        return $this->data->replace(
            $this->loadTemplate('Shared', 'basic'),
            [
                '{{content|raw}}' => $content,
                '{{ links.companies }}' => "/{$locale->value()}/companies",
                '{{ links.games }}' => "/{$locale->value()}/games",
                '{{ links.players }}' => "/{$locale->value()}/players",
            ]
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

    public function removePlaceholders(string $value): string
    {
        return $this->removePlaceholderPair(
            $this->removePlaceholderPair($value, '{{', '}}'),
            '{%',
            '%}'
        );
    }

    private function removePlaceholderPair(
        string $value,
        string $begin,
        string $end
    ): string {
        $delim = '/';

        $replaced = preg_replace(
            $delim . preg_quote($begin, $delim)
            . '.+?'
            . preg_quote($end, $delim) . $delim,
            '',
            $value
        );

        if ($replaced === null) {
            throw new \UnexpectedValueException('Error replacing placeholders');
        }

        return $replaced;
    }
}
