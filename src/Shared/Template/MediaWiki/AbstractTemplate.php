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

use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\MediaWiki\SharedTemplates;
use Stg\HallOfRecords\Shared\Template\Renderer;

abstract class AbstractTemplate
{
    private Renderer $renderer;
    private SharedTemplates $sharedTemplates;
    private Routes $routes;

    public function __construct(
        Renderer $renderer,
        SharedTemplates $sharedTemplates,
        Routes $routes
    ) {
        $this->renderer = $this->initRenderer($renderer);
        $this->sharedTemplates = $sharedTemplates;
        $this->routes = $routes;
    }

    abstract protected function initRenderer(Renderer $renderer): Renderer;

    public function renderer(): Renderer
    {
        return $this->renderer;
    }

    public function sharedTemplates(): SharedTemplates
    {
        return $this->sharedTemplates;
    }

    public function routes(): Routes
    {
        return $this->routes;
    }

    /**
     * @return static
     */
    protected function withLocale(Locale $locale): self
    {
        $clone = clone $this;
        $clone->renderer = $this->renderer->withLocale($locale);
        $clone->sharedTemplates = $this->sharedTemplates->withLocale($locale);
        $clone->routes = $this->routes->withLocale($locale);

        return $clone;
    }
}
