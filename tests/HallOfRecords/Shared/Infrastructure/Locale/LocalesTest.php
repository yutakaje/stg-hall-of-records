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

namespace Tests\HallOfRecords\Shared\Infrastructure\Locale;

use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;

class LocalesTest extends \Tests\TestCase
{
    public function testWithLocales(): void
    {
        $locales = new Locales([
            'kr',
            'en',
            'ja',
        ]);

        // First value should be used as default.
        self::assertSame(['kr', 'en', 'ja'], $locales->all());
        self::assertSame('kr', $locales->default());
        self::assertTrue($locales->exists('kr'));
        self::assertTrue($locales->exists('en'));
        self::assertTrue($locales->exists('ja'));
        self::assertFalse($locales->exists('es'));
        self::assertFalse($locales->exists('de'));
    }
}
