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

namespace Tests\HallOfRecords\Shared\Template;

use Stg\HallOfRecords\Shared\Template\Renderer;

class RendererTest extends \Tests\TestCase
{
    public function testImmutability(): void
    {
        $renderer = Renderer::createWithFiles(__DIR__);

        self::assertNotEquals($renderer, $renderer->withLocale('en'));
    }

    public function testLocaleAwareness(): void
    {
        $renderer = Renderer::createWithFiles(__DIR__);
        $context = [
            'contents' => '{{ contents }}',
        ];

        self::assertSame(
            $this->loadFile(__DIR__ . '/template.twig'),
            $renderer->render('template', $context)
        );
        self::assertSame(
            $this->loadFile(__DIR__ . '/template.ja.twig'),
            $renderer->withLocale('ja')->render('template', $context)
        );
        self::assertSame(
            $this->loadFile(__DIR__ . '/template.twig'),
            $renderer->withLocale('en')->render('template', $context)
        );
    }

    public function testRendering(): void
    {
        $renderer = Renderer::createWithFiles(__DIR__);
        $contents = base64_encode(random_bytes(128));

        self::assertSame(
            str_replace(
                '{{ contents }}',
                $contents,
                $this->loadFile(__DIR__ . '/template.twig')
            ),
            $renderer->render('template', [
                'contents' => $contents,
            ])
        );
    }
}
