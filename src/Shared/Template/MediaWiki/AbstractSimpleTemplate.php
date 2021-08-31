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
use Stg\HallOfRecords\Shared\Template\Renderer;

abstract class AbstractSimpleTemplate
{
    private Renderer $renderer;
    private Routes $routes;

    public function __construct(
        Renderer $renderer,
        Routes $routes
    ) {
        $this->renderer = $this->initRenderer($renderer);
        $this->routes = $routes;
    }

    abstract protected function initRenderer(Renderer $renderer): Renderer;

    public function renderer(): Renderer
    {
        return $this->renderer;
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
        $clone->routes = $this->routes->withLocale($locale);

        return $clone;
    }
}
