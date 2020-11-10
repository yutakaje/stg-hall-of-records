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

namespace Stg\HallOfRecords\Export;

final class Formatter
{
    private string $locale;

    public function __construct(string $locale = '')
    {
        $this->locale = $locale;
    }

    /**
     * @param mixed $date
     * @return mixed
     */
    public function formatDate($date)
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

        if ($this->locale === 'en') {
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
        } elseif ($this->locale === 'jp') {
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

    private function dayNameEn(string $day): string
    {
        switch ($day) {
            case '01':
                return '1st';
            case '02':
                return '2nd';
            case '03':
                return '3rd';
            default:
                return ltrim($day, '0') . 'th';
        }
    }
}
