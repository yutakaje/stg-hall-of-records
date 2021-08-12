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

namespace Stg\HallOfRecords\Shared\Application\Query;

use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

abstract class AbstractResult
{
    private Locale $locale;

    public function __construct(Locale $locale)
    {
        $this->locale = $locale;
    }

    public function locale(): Locale
    {
        return $this->locale;
    }
}
