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

/**
 * @phpstan-type Aliases string[]
 */
final class PlayerRecord extends AbstractRecord
{
    private string $name;
    /** @var Aliases */
    private array $aliases;

    /**
     * @param Aliases $aliases
     */
    public function __construct(
        string $name,
        array $aliases
    ) {
        parent::__construct();
        $this->name = $name;
        $this->aliases = $aliases;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return Aliases
     */
    public function aliases(): array
    {
        return $this->aliases;
    }
}
