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

namespace Stg\HallOfRecords\Database;

/**
 * @phpstan-type IntGenerator \Generator<int>
 */
final class IdGenerator
{
    /** @var IntGenerator */
    private \Generator $generator;

    public function __construct()
    {
        $this->generator = $this->createGenerator();
    }

    public function nextId(): int
    {
        $value = $this->generator->current();
        $this->generator->next();
        return $value;
    }

    /**
     * @return IntGenerator $generator
     */
    private function createGenerator(): \Generator
    {
        $id = 1;
        while (true) {
            yield $id++;
        }
    }
}
