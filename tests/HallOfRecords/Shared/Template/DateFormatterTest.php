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

use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\DateFormatter;

class DateFormatterTest extends \Tests\TestCase
{
    public function testFormatDateWithDefaultLocale(): void
    {
        $formatter = new DateFormatter();
        $locale = new Locale('none');

        $format = fn ($date) => $formatter->format($locale, $date);

        self::assertSame('2020-09-03', $format('2020-09-03'));
        self::assertSame('2020-09', $format('2020-09'));
        self::assertSame('2020', $format('2020'));
        self::assertSame('unknown, 2020-09-03?', $format('unknown, 2020-09-03?'));
        self::assertSame('unknown, 2020-09?', $format('unknown, 2020-09?'));
        self::assertSame('unknown, 2020?', $format('unknown, 2020?'));
    }

    public function testFormatDateWithEnLocale(): void
    {
        $formatter = new DateFormatter();
        $locale = new Locale('en');

        $format = fn ($date) => $formatter->format($locale, $date);

        self::assertSame('September 3rd, 2020', $format('2020-09-03'));
        self::assertSame('September 2020', $format('2020-09'));
        self::assertSame('2020', $format('2020'));
        self::assertSame('unknown, September 3rd, 2020?', $format('unknown, 2020-09-03?'));
        self::assertSame('unknown, September 2020?', $format('unknown, 2020-09?'));
        self::assertSame('unknown, 2020?', $format('unknown, 2020?'));

        self::assertSame('January 1st, 2020', $format('2020-01-01'));
        self::assertSame('February 2nd, 2020', $format('2020-02-02'));
        self::assertSame('March 3rd, 2020', $format('2020-03-03'));
        self::assertSame('April 4th, 2020', $format('2020-04-04'));
        self::assertSame('May 5th, 2020', $format('2020-05-05'));
        self::assertSame('June 6th, 2020', $format('2020-06-06'));
        self::assertSame('July 7th, 2020', $format('2020-07-07'));
        self::assertSame('August 8th, 2020', $format('2020-08-08'));
        self::assertSame('September 9th, 2020', $format('2020-09-09'));
        self::assertSame('October 10th, 2020', $format('2020-10-10'));
        self::assertSame('November 11th, 2020', $format('2020-11-11'));
        self::assertSame('December 12th, 2020', $format('2020-12-12'));
        for ($day = 13; $day <= 20; ++$day) {
            self::assertSame("January {$day}th, 2020", $format("2020-01-{$day}"));
        }
        self::assertSame('January 21st, 2020', $format('2020-01-21'));
        self::assertSame('January 22nd, 2020', $format('2020-01-22'));
        self::assertSame('January 23rd, 2020', $format('2020-01-23'));
        for ($day = 24; $day <= 30; ++$day) {
            self::assertSame("January {$day}th, 2020", $format("2020-01-{$day}"));
        }
        self::assertSame('January 31st, 2020', $format('2020-01-31'));
    }

    public function testFormatDateWithJaLocale(): void
    {
        $formatter = new DateFormatter();
        $locale = new Locale('ja');

        $format = fn ($date) => $formatter->format($locale, $date);

        self::assertSame('2020年09月03日', $format('2020-09-03'));
        self::assertSame('2020年09月', $format('2020-09'));
        self::assertSame('2020年', $format('2020'));
        self::assertSame('unknown, 2020年09月03日?', $format('unknown, 2020-09-03?'));
        self::assertSame('unknown, 2020年09月?', $format('unknown, 2020-09?'));
        self::assertSame('unknown, 2020年?', $format('unknown, 2020?'));
    }

    public function testFormatDateWithEnLocaleAndShortFormat(): void
    {
        $formatter = new DateFormatter(DateFormatter::FORMAT_SHORT);
        $locale = new Locale('en');

        $format = fn ($date) => $formatter->format($locale, $date);

        self::assertSame('Sep 3, 2020', $format('2020-09-03'));
        self::assertSame('Sep 2020', $format('2020-09'));
        self::assertSame('2020', $format('2020'));
        self::assertSame('unknown, Sep 3, 2020?', $format('unknown, 2020-09-03?'));
        self::assertSame('unknown, Sep 2020?', $format('unknown, 2020-09?'));
        self::assertSame('unknown, 2020?', $format('unknown, 2020?'));

        self::assertSame('Jan 1, 2020', $format('2020-01-01'));
        self::assertSame('Feb 2, 2020', $format('2020-02-02'));
        self::assertSame('Mar 3, 2020', $format('2020-03-03'));
        self::assertSame('Apr 4, 2020', $format('2020-04-04'));
        self::assertSame('May 5, 2020', $format('2020-05-05'));
        self::assertSame('Jun 6, 2020', $format('2020-06-06'));
        self::assertSame('Jul 7, 2020', $format('2020-07-07'));
        self::assertSame('Aug 8, 2020', $format('2020-08-08'));
        self::assertSame('Sep 9, 2020', $format('2020-09-09'));
        self::assertSame('Oct 10, 2020', $format('2020-10-10'));
        self::assertSame('Nov 11, 2020', $format('2020-11-11'));
        self::assertSame('Dec 12, 2020', $format('2020-12-12'));
        for ($day = 13; $day <= 31; ++$day) {
            self::assertSame("Jan {$day}, 2020", $format("2020-01-{$day}"));
        }
    }

    public function testFormatDateWithJaLocaleAndShortFormat(): void
    {
        $formatter = new DateFormatter(DateFormatter::FORMAT_SHORT);
        $locale = new Locale('ja');

        $format = fn ($date) => $formatter->format($locale, $date);

        self::assertSame('2020年09月03日', $format('2020-09-03'));
        self::assertSame('2020年09月', $format('2020-09'));
        self::assertSame('2020年', $format('2020'));
        self::assertSame('unknown, 2020年09月03日?', $format('unknown, 2020-09-03?'));
        self::assertSame('unknown, 2020年09月?', $format('unknown, 2020-09?'));
        self::assertSame('unknown, 2020年?', $format('unknown, 2020?'));
    }
}
