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
use Stg\HallOfRecords\Shared\Template\MediaWiki\AbstractTemplate;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class IndexTemplate extends AbstractTemplate implements IndexTemplateInterface
{
    protected function initRenderer(Renderer $renderer): Renderer
    {
        return $renderer->withTemplateFiles(__DIR__ . '/html/index');
    }

    public function respond(
        ResponseInterface $response,
        Locale $locale
    ): ResponseInterface {
        $response->getBody()->write(
            $this->withLocale($locale)->createOutput()
        );
        return $response;
    }

    private function createOutput(): string
    {
        return $this->sharedTemplates()->main(
            $this->renderIndex(),
            $this->routes()->forEachLocale(
                fn ($routes) => $routes->index()
            )
        );
    }

    private function renderIndex(): string
    {
        return $this->renderer()->render('main', [
            'links' => [
                'companies' => $this->routes()->listCompanies(),
                'games' => $this->routes()->listGames(),
                'players' => $this->routes()->listPlayers(),
            ],
        ]);
    }
}
