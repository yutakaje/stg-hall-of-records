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

namespace Stg\HallOfRecords\Shared\Infrastructure\Type;

final class Locale
{
    private string $locale;

    public function __construct(string $locale)
    {
        if ($locale === '') {
            throw new \InvalidArgumentException('Locale may not be empty');
        }

        $this->locale = $locale;
    }

    public function value(): string
    {
        return $this->locale;
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
