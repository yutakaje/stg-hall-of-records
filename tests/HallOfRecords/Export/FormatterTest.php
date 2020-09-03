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

namespace Tests\HallOfRecords\Export;

use Stg\HallOfRecords\Export\Formatter;

class FormatterTest extends \Tests\TestCase
{
    public function testFormatDateWithDefaultLocale(): void
    {
        $formatter = new Formatter();

        self::assertSame('2020-09-03', $formatter->formatDate('2020-09-03'));
        self::assertSame('2020-09', $formatter->formatDate('2020-09'));
        self::assertSame('2020', $formatter->formatDate('2020'));
        self::assertSame('unknown, 2020-09-03?', $formatter->formatDate('unknown, 2020-09-03?'));
        self::assertSame('unknown, 2020-09?', $formatter->formatDate('unknown, 2020-09?'));
        self::assertSame('unknown, 2020?', $formatter->formatDate('unknown, 2020?'));
    }

    public function testFormatDateWithEnLocale(): void
    {
        $formatter = new Formatter('en');

        self::assertSame('09/03/2020', $formatter->formatDate('2020-09-03'));
        self::assertSame('09/2020', $formatter->formatDate('2020-09'));
        self::assertSame('2020', $formatter->formatDate('2020'));
        self::assertSame('unknown, 09/03/2020?', $formatter->formatDate('unknown, 2020-09-03?'));
        self::assertSame('unknown, 09/2020?', $formatter->formatDate('unknown, 2020-09?'));
        self::assertSame('unknown, 2020?', $formatter->formatDate('unknown, 2020?'));
    }

    public function testFormatDateWithJpLocale(): void
    {
        $formatter = new Formatter('jp');

        self::assertSame('2020年09月03日', $formatter->formatDate('2020-09-03'));
        self::assertSame('2020年09月', $formatter->formatDate('2020-09'));
        self::assertSame('2020年', $formatter->formatDate('2020'));
        self::assertSame('unknown, 2020年09月03日?', $formatter->formatDate('unknown, 2020-09-03?'));
        self::assertSame('unknown, 2020年09月?', $formatter->formatDate('unknown, 2020-09?'));
        self::assertSame('unknown, 2020年?', $formatter->formatDate('unknown, 2020?'));
    }
}
