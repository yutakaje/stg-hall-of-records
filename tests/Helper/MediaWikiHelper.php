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
    private LocaleHelper $localizer;
    private DataHelper $data;

    public function __construct(
        FilesystemHelper $filesystem,
        LocaleHelper $localizer,
        DataHelper $data,
    ) {
        $this->filesystem = $filesystem;
        $this->localizer = $localizer;
        $this->data = $data;
    }

    public function loadTemplate(string $context, string $name): string
    {
        $rootDir = $this->filesystem->rootDir();

        return $this->filesystem->loadFile(
            "{$rootDir}/src/{$context}/Template/MediaWiki/html/{$name}.twig"
        );
    }

    public function loadMainTemplate(
        string $content,
        Locale $locale,
        string $selfLink
    ): string {
        return $this->data->replace(
            $this->loadTemplate('Shared', 'basic'),
            array_merge(
                [
                    '{{ content|raw }}' => $content,
                    '{{ locale }}' => $locale->value(),
                    '{{ links.index }}' => "/{$locale}",
                    '{{ links.companies }}' => "/{$locale}/companies",
                    '{{ links.games }}' => "/{$locale}/games",
                    '{{ links.players }}' => "/{$locale}/players",
                ],
                $this->localizeSelfLink($selfLink)
            )
        );
    }

    /**
     * @return array<string,string>
     */
    private function localizeSelfLink(string $link): array
    {
        $links = [];

        foreach ($this->localizer->all() as $locale) {
            $links["{{ links.self.{$locale} }}"] = str_replace(
                '{locale}',
                $locale->value(),
                $link
            );
        }

        return $links;
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
