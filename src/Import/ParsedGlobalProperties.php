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

namespace Stg\HallOfRecords\Import;

final class ParsedGlobalProperties
{
    private string $description;
    /** @var array<string,string> */
    private array $templates;

    /**
     * @param array<string,string> $templates
     */
    public function __construct(
        string $description,
        array $templates
    ) {
        $this->description = $description;
        $this->templates = $templates;
    }

    public function description(): string
    {
        return $this->description;
    }

    /**
     * @return array<string,string>
     */
    public function templates(): array
    {
        return $this->templates;
    }
}
