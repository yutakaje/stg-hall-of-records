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
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

class LocalesTest extends \Tests\TestCase
{
    public function testWithLocales(): void
    {
        $entries = [
            new Locale('kr'),
            new Locale('en'),
            new Locale('ja'),
        ];

        $locales = new Locales('en', $entries);

        self::assertSame($entries, $locales->all());
        self::assertSame($entries[1], $locales->default());
        self::assertTrue($locales->exists('kr'));
        self::assertTrue($locales->exists('en'));
        self::assertTrue($locales->exists('ja'));
        self::assertFalse($locales->exists('es'));
        self::assertFalse($locales->exists('de'));
        self::assertSame($entries[0], $locales->get('kr'));
        self::assertSame($entries[1], $locales->get('en'));
        self::assertSame($entries[2], $locales->get('ja'));
    }
}
