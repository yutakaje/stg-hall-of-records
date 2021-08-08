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

namespace Tests\HallOfRecords\Player\MediaWiki;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Helper\Data\PlayerEntry;
use Tests\Helper\Data\GameEntry;

class ViewPlayerTest extends \Tests\TestCase
{
    public function testWithDefaultLocale(): void
    {
        $player = $this->createPlayer();

        $request = $this->http()->createServerRequest('GET', '/players/{id}');

        $this->executeTest($player, $request, $this->locale()->default());
    }

    public function testWithRandomLocale(): void
    {
        $player = $this->createPlayer();

        $locale = $this->locale()->random();

        $request = $this->http()->createServerRequest('GET', '/players/{id}')
            ->withHeader('Accept-Language', $locale);

        $this->executeTest($player, $request, $locale);
    }

    public function testWithAliases(): void
    {
        $player = $this->createPlayer(['Reddo Arimaa', 'Red Arimer']);

        $request = $this->http()->createServerRequest('GET', '/players/{id}');

        $this->executeTest($player, $request, $this->locale()->default());
    }

    private function executeTest(
        PlayerEntry $player,
        ServerRequestInterface $request,
        string $locale
    ): void {
        $this->insertPlayer($player);

        $request = $this->http()->replaceInUriPath(
            $request,
            '{id}',
            (string)$player->id()
        );

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->mediaWiki()->canonicalizeHtml(
                $this->createOutput($player, $locale)
            ),
            $this->mediaWiki()->canonicalizeHtml(
                (string)$response->getBody()
            )
        );
    }

    /**
     * @param string[] $aliases
     */
    private function createPlayer(array $aliases = []): PlayerEntry
    {
        return $this->data()->createPlayer('Akuma', $aliases);
    }

    private function insertPlayer(PlayerEntry $player): void
    {
        $this->data()->insertPlayer($player);
    }

    private function createOutput(PlayerEntry $player, string $locale): string
    {
        return str_replace(
            '{{content|raw}}',
            $this->createPlayerOutput($player, $locale),
            $this->mediaWiki()->loadTemplate('Shared', 'basic')
        );
    }

    private function createPlayerOutput(
        PlayerEntry $player,
        string $locale
    ): string {
        return str_replace(
            [
                '{{ player.id }}',
                '{{ player.name }}',
                '{{ player.link }}',
                '{{ player.aliases|raw }}',
            ],
            [
                $player->id(),
                $player->name(),
                "/players/{$player->id()}",
                $this->createAliasesOutput($player->aliases(), $locale),
            ],
            $this->mediaWiki()->loadTemplate('Player', 'view-player/main')
        );
    }

    /**
     * @param string[] $aliases
     */
    private function createAliasesOutput(
        array $aliases,
        string $locale
    ): string {
        if ($aliases == null) {
            return '';
        }

        sort($aliases);

        return $this->mediaWiki()->removePlaceholders(str_replace(
            "{{ aliases|join(', ') }}",
            implode(', ', $aliases),
            $this->mediaWiki()->loadTemplate('Player', 'view-player/aliases-list')
        ));
    }
}
