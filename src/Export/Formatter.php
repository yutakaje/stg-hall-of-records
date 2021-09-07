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

use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\DateFormatter;

final class Formatter
{
    public const DATE_FORMAT_SHORT = DateFormatter::FORMAT_SHORT;
    public const DATE_FORMAT_LONG = DateFormatter::FORMAT_LONG;

    private Locale $locale;
    private DateFormatter $formatter;

    public function __construct(string $locale = '', string $dateFormat = '')
    {
        $this->locale = $this->mapLocale($locale);
        $this->formatter = new DateFormatter($dateFormat);
    }

    /**
     * @param mixed $date
     * @return mixed
     */
    public function formatDate($date)
    {
        return $this->formatter->format($this->locale, $date);
    }

    private function mapLocale(string $locale): Locale
    {
        switch ($locale) {
            case 'jp':
                return new Locale('ja');

            case '':
                return new Locale('none');

            default:
                return new Locale($locale);
        }
    }
}
