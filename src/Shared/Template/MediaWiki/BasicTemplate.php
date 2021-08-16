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
use Stg\HallOfRecords\Shared\Template\Renderer;

final class BasicTemplate
{
    private Renderer $renderer;
    private Routes $routes;

    public function __construct(Routes $routes)
    {
        $this->renderer = Renderer::createWithFiles(__DIR__ . '/html');
        $this->routes = $routes;
    }

    /**
     * @param array<string,string> $links
     */
    public function render(
        Locale $locale,
        string $content,
        array $links = []
    ): string {
        $routes = $this->routes->withLocale($locale);

        return $this->renderer->withLocale($locale)
            ->render('basic', [
                'content' => $content,
                'links' => $links + [
                    'companies' => $routes->listCompanies(),
                    'games' => $routes->listGames(),
                    'players' => $routes->listPlayers(),
                ],
            ]);
    }
}
