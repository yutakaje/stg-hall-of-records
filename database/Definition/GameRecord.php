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
 * @phpstan-type LocalizedValues array<string,string>
 * @phpstan-type Links array{url:string, title:string}[]
 * @phpstan-type LocalizedLinks array<string,Links>
 */
final class GameRecord extends AbstractRecord
{
    private int $companyId;
    /** @var LocalizedValues */
    private array $names;
    /** @var LocalizedValues */
    private array $translitNames;
    /** @var LocalizedValues */
    private array $descriptions;
    /** @var LocalizedLinks */
    private array $links;

    /**
     * @param LocalizedValues $names
     * @param LocalizedValues $translitNames
     * @param LocalizedValues $descriptions
     * @param LocalizedLinks $links
     */
    public function __construct(
        int $companyId,
        array $names,
        array $translitNames,
        array $descriptions,
        array $links
    ) {
        parent::__construct();
        $this->companyId = $companyId;
        $this->names = $names;
        $this->translitNames = $translitNames;
        $this->descriptions = $descriptions;
        $this->links = $links;
    }

    public function companyId(): int
    {
        return $this->companyId;
    }

    public function name(Locale $locale): string
    {
        return $this->localizedValue($this->names, $locale);
    }

    public function translitName(Locale $locale): string
    {
        return $this->localizedValue($this->translitNames, $locale);
    }

    public function description(Locale $locale): string
    {
        return $this->localizedValue($this->descriptions, $locale);
    }

    /**
     * @return Links
     */
    public function links(Locale $locale): array
    {
        return $this->localizedValue($this->links, $locale);
    }
}
