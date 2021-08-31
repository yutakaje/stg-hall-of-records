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

abstract class AbstractSimpleTemplate
{
    private Renderer $renderer;
    private Routes $routes;
    private TranslatorInterface $translator;

    public function __construct(
        Renderer $renderer,
        Routes $routes,
        TranslatorInterface $translator
    ) {
        $this->renderer = $this->initRenderer($renderer);
        $this->routes = $routes;
        $this->translator = $translator;
    }

    abstract protected function initRenderer(Renderer $renderer): Renderer;

    protected function renderer(): Renderer
    {
        return $this->renderer;
    }

    protected function routes(): Routes
    {
        return $this->routes;
    }

    protected function translator(): TranslatorInterface
    {
        return $this->translator;
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
