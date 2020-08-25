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

namespace Stg\HallOfRecords\Export\MediaWiki;

final class Layout
{
    /** @var array<string,string> */
    private array $templates;
    /** @var array<string,mixed[]> */
    private array $sort;

    /**
     * @param array<string,string> $templates
     * @param array<string,mixed[]> $sort
     */
    public function __construct(
        array $templates,
        array $sort
    ) {
        $this->templates = $templates;
        $this->sort = $sort;
    }

    /**
     * @param array<string,mixed> $properties
     */
    public static function createFromArray(array $properties): self
    {
        return new self(
            $properties['templates'] ?? [],
            $properties['sort'] ?? []
        );
    }

    /**
     * @return array<string,string>
     */
    public function templates(): array
    {
        return $this->templates;
    }

    public function template(string $name): ?string
    {
        return $this->templates[$name] ?? null;
    }

    /**
     * @return array<string,mixed>
     */
    public function sort(string $name): array
    {
        return $this->sort[$name] ?? [];
    }
}
