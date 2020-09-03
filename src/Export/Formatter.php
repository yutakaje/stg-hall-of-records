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

    public function formatDate(string $date): string
    {
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
                        $date = "{$match['day']}/{$date}";
                    }
                    $date = "{$match['month']}/{$date}";
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
}
