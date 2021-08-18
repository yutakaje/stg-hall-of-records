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

namespace Tests\HallOfRecords\Shared\MediaWiki;

use Fig\Http\Message\StatusCodeInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

class IndexTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        $this->testWithLocale($this->locale()->get('en'));
    }

    public function testWithJaLocale(): void
    {
        $this->testWithLocale($this->locale()->get('ja'));
    }

    private function testWithLocale(Locale $locale): void
    {
        $request = $this->http()->createServerRequest('GET', "/{$locale}");

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->mediaWiki()->canonicalizeHtml(
                $this->createOutput($locale)
            ),
            $this->mediaWiki()->canonicalizeHtml(
                (string)$response->getBody()
            )
        );
    }

    private function createOutput(Locale $locale): string
    {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadBasicTemplate(
                    $this->createIndexOutput($locale),
                    $locale,
                    '/{locale}'
                )
            )
        );
    }

    private function createIndexOutput(Locale $locale): string
    {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Shared', 'index/main'),
            [
                '{{ links.company }}' => "/{$locale}/companies",
                '{{ links.games }}' => "/{$locale}/games",
                '{{ links.players }}' => "/{$locale}/players",
            ]
        );
    }
}
