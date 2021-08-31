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

use Stg\HallOfRecords\Shared\Application\ResultMessage;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class BasicTemplate extends AbstractSimpleTemplate
{
    protected function initRenderer(Renderer $renderer): Renderer
    {
        return $renderer->withTemplateFiles(__DIR__ . '/html');
    }

    /**
     * @param array<string,string> $selfLinks
     */
    public function render(
        Locale $locale,
        string $content,
        array $selfLinks,
        ResultMessage $message
    ): string {
        return $this->withLocale($locale)->doRender(
            $content,
            $selfLinks,
            $message,
            $locale
        );
    }

    /**
     * @param array<string,string> $selfLinks
     */
    private function doRender(
        string $content,
        array $selfLinks,
        ResultMessage $message,
        Locale $locale
    ): string {
        return $this->renderer()->render('basic', [
            'content' => $content,
            'links' => [
                'self' => $selfLinks,
                'index' => $this->routes()->index(),
                'companies' => $this->routes()->listCompanies(),
                'games' => $this->routes()->listGames(),
                'players' => $this->routes()->listPlayers(),
            ],
            'message' => $this->renderMessage($message, $locale),
        ]);
    }

    private function renderMessage(
        ResultMessage $message,
        Locale $locale
    ): string {
        return $this->renderer()->render('message', [
            'type' => $message->type(),
            'message' => $this->translator()->trans(
                $locale,
                $message->message(),
                $message->attributes()
            ),
        ]);
    }
}
