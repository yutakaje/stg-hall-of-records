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

use Psr\Http\Message\ResponseInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\IndexTemplateInterface;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class IndexTemplate implements IndexTemplateInterface
{
    private Renderer $renderer;
    private BasicTemplate $wrapper;
    private Routes $routes;

    public function __construct(
        Renderer $renderer,
        BasicTemplate $wrapper,
        Routes $routes
    ) {
        $this->renderer = $renderer->withTemplateFiles(
            __DIR__ . '/html/index'
        );
        $this->wrapper = $wrapper;
        $this->routes = $routes;
    }

    public function respond(
        ResponseInterface $response,
        Locale $locale
    ): ResponseInterface {
        $response->getBody()->write($this->createOutput($locale));
        return $response;
    }

    private function createOutput(Locale $locale): string
    {
        $routes = $this->routes->withLocale($locale);

        return $this->wrapper->render(
            $locale,
            $this->renderIndex(
                $this->renderer->withLocale($locale),
                $routes->withLocale($locale)
            ),
            $this->routes->forEachLocale(
                fn ($routes) => $routes->index()
            )
        );
    }

    private function renderIndex(
        Renderer $renderer,
        Routes $routes
    ): string {
        return $renderer->render('main', [
            'links' => [
                'companies' => $routes->listCompanies(),
                'games' => $routes->listGames(),
                'players' => $routes->listPlayers(),
            ],
        ]);
    }
}
