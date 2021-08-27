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

use Stg\HallOfRecords\Shared\Application\Query\Filter\Condition;
use Stg\HallOfRecords\Shared\Application\Query\Filter\FilterException;

/**
 * @phpstan-type Token string
 */
final class Filter
{
    private const OP_AND = 'and';

    private string $query;

    public function __construct(string $query = '')
    {
        $this->query = $query;
    }

    public function query(): string
    {
        return $this->query;
    }

    /**
     * @return Condition[]
     */
    public function conditions(): array
    {
        return $this->parse(
            $this->scan()
        );
    }

    /**
     * @return Token[]
     */
    private function scan(): array
    {
        $tokens = [];

        $index = 0;
        $isIncomplete = false;
        foreach (explode(' ', $this->query) as $token) {
            if (strpos($token, '"') === 0) {
                $tokens[$index] = $token;
                $isIncomplete = true;
            } elseif (substr($token, -1) === '"') {
                $tokens[$index++] .= " {$token}";
                $isIncomplete = false;
            } else {
                if ($isIncomplete) {
                    $tokens[$index] .= " {$token}";
                } else {
                    $tokens[$index++] = $token;
                }
            }
        }

        return $tokens;
    }

    /**
     * @param Token[] $tokens
     * @return Condition[]
     */
    private function parse(array $tokens): array
    {
        $name = array_shift($tokens);
        $operator = array_shift($tokens);
        $value = array_shift($tokens);

        if ($name === null || $operator === null || $value === null) {
            return [];
        }

        // We only support AND at the moment.
        $conjunction = array_shift($tokens);
        if ($conjunction !== null && $conjunction !== self::OP_AND) {
            throw FilterException::invalidConjunction($conjunction);
        }

        return array_merge(
            [new Condition($name, $operator, $value)],
            $this->parse($tokens)
        );
    }
}
