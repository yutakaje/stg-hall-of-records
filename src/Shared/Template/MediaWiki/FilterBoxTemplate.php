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

use Stg\HallOfRecords\Shared\Application\Query\Filter;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class FilterBoxTemplate
{
    private Renderer $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer->withTemplateFiles(__DIR__ . '/html');
    }

    public function render(
        Locale $locale,
        Filter $filter,
        string $example
    ): string {
        return $this->renderer->withLocale($locale)
            ->render('filter-box', [
                'filterValue' => $filter->query(),
                'example' => $example,
            ]);
    }
}
