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

use Stg\HallOfRecords\Shared\Infrastructure\Locale\TranslatorInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\Renderer;

abstract class AbstractTemplate extends AbstractSimpleTemplate
{
    private SharedTemplates $sharedTemplates;

    public function __construct(
        Renderer $renderer,
        Routes $routes,
        TranslatorInterface $translator,
        SharedTemplates $sharedTemplates,
    ) {
        parent::__construct($renderer, $routes, $translator);
        $this->sharedTemplates = $sharedTemplates;
    }

    protected function sharedTemplates(): SharedTemplates
    {
        return $this->sharedTemplates;
    }

    /**
     * @return static
     */
    protected function withLocale(Locale $locale): self
    {
        $clone = parent::withLocale($locale);
        $clone->sharedTemplates = $this->sharedTemplates->withLocale($locale);

        return $clone;
    }
}
