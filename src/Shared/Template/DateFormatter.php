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

namespace Stg\HallOfRecords\Shared\Template;

use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

final class DateFormatter
{
    public const FORMAT_SHORT = 'short';
    public const FORMAT_LONG = 'long';

    /** @var self::FORMAT_* */
    private string $dateFormat;

    public function __construct(string $dateFormat = '')
    {
        $this->dateFormat = $dateFormat === self::FORMAT_SHORT
            ? self::FORMAT_SHORT
            : self::FORMAT_LONG;
    }

    /**
     * @param mixed $date
     * @return mixed
     */
    public function format(Locale $locale, $date)
    {
        if (!is_string($date)) {
            return $date;
        }

        $pattern = '/'
            . '(?<year>[1-9][0-9]{3})'
            . '(?:-(?<month>[0-1][0-9])'
            . '(?:-(?<day>[0-3][0-9])'
            . ')?)?'
            . '/';

        if ($locale->value() === 'en') {
            $callback = function (array $match): string {
                $date = $match['year'];
                if (isset($match['month'])) {
                    if (isset($match['day'])) {
                        $date = $this->dayNameEn($match['day']) . ", {$date}";
                    }
                    $date = $this->monthNameEn($match['month']) . " {$date}";
                }
                return $date;
            };
        } elseif ($locale->value() === 'ja') {
            $callback = function (array $match): string {
                $date = "{$match['year']}年";
                if (isset($match['month'])) {
                    $date .= "{$match['month']}月";
                    if (isset($match['day'])) {
                        $date .= "{$match['day']}日";
                    }
                }
                return $date;
            };
        } else {
            $callback = function (array $match): string {
                $date = $match['year'];
                if (isset($match['month'])) {
                    $date .= "-{$match['month']}";
                    if (isset($match['day'])) {
                        $date .= "-{$match['day']}";
                    }
                }
                return $date;
            };
        }

        $formatted = preg_replace_callback($pattern, $callback, $date);

        return $formatted ?? $date;
    }

    private function monthNameEn(string $month): string
    {
        if ($this->dateFormat === self::FORMAT_SHORT) {
            return $this->monthNameEnShort($month);
        } else {
            return $this->monthNameEnLong($month);
        };
    }

    private function monthNameEnLong(string $month): string
    {
        switch ($month) {
            case '01':
                return 'January';
            case '02':
                return 'February';
            case '03':
                return 'March';
            case '04':
                return 'April';
            case '05':
                return 'May';
            case '06':
                return 'June';
            case '07':
                return 'July';
            case '08':
                return 'August';
            case '09':
                return 'September';
            case '10':
                return 'October';
            case '11':
                return 'November';
            case '12':
                return 'December';
            default:
                return '';
        }
    }

    private function monthNameEnShort(string $month): string
    {
        return substr($this->monthNameEnLong($month), 0, 3);
    }

    private function dayNameEn(string $day): string
    {
        if ($this->dateFormat === self::FORMAT_SHORT) {
            return $this->dayNameEnShort($day);
        } else {
            return $this->dayNameEnLong($day);
        };
    }

    private function dayNameEnLong(string $day): string
    {
        $day = $this->dayNameEnShort($day);

        switch ($day) {
            case '1':
                return '1st';
            case '2':
                return '2nd';
            case '3':
                return '3rd';
            case '21':
                return '21st';
            case '22':
                return '22nd';
            case '23':
                return '23rd';
            case '31':
                return '31st';
            default:
                return "{$day}th";
        }
    }

    private function dayNameEnShort(string $day): string
    {
        return ltrim($day, '0');
    }
}
