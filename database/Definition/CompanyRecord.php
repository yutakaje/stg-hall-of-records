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

namespace Stg\HallOfRecords\Database\Definition;

use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

/**
 * @phpstan-type Names array<string,string>
 */
final class CompanyRecord extends AbstractRecord
{
    /** @var Names */
    private array $names;
    /** @var Names */
    private array $translitNames;

    /**
     * @param Names $names
     * @param Names $translitNames
     */
    public function __construct(array $names, array $translitNames)
    {
        parent::__construct();
        $this->names = $names;
        $this->translitNames = $translitNames;
    }

    public function name(Locale $locale): string
    {
        return $this->localizedValue($this->names, $locale);
    }

    public function translitName(Locale $locale): string
    {
        return $this->localizedValue($this->translitNames, $locale);
    }
}
