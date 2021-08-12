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

namespace Tests\HallOfRecords\Shared\Infrastructure\Type;

use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

class LocaleTest extends \Tests\TestCase
{
    public function testWithValue(): void
    {
        $locale = new Locale('kr');

        self::assertSame('kr', $locale->value());
        self::assertSame('kr', (string)$locale);
    }
}
