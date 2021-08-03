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

namespace Stg\HallOfRecords\Shared\Template\MediaWiki;

use Stg\HallOfRecords\Shared\Template\Renderer;

final class BasicTemplate
{
    private Renderer $renderer;

    public function __construct()
    {
        $this->renderer = Renderer::createWithFiles(__DIR__ . '/html');
    }

    public function render(string $locale, string $content): string
    {
        return $this->renderer->withLocale($locale)
            ->render('basic', [
                'content' => $content,
            ]);
    }
}
