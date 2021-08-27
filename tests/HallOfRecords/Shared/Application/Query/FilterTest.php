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

namespace Tests\HallOfRecords\Shared\Application\Query;

use Stg\HallOfRecords\Shared\Application\Query\Filter;
use Stg\HallOfRecords\Shared\Application\Query\Filter\Condition;
use Stg\HallOfRecords\Shared\Application\Query\Filter\Operator;

/**
 * @phpstan-type DumpedCondition array{name:string, operator:Operator, value:string}
 */
class FilterTest extends \Tests\TestCase
{
    public function testWithSingleCondition(): void
    {
        $condition = $this->randomCondition();
        $query = "{$condition->name} {$condition->operator} {$condition->value}";

        $filter = new Filter($query);

        self::assertSame($query, $filter->query());
        self::assertEquals(
            $this->dumpConditions([
                new Condition($condition->name, $condition->operator, $condition->value),
            ]),
            $this->dumpConditions($filter->conditions())
        );
    }

    public function testWithMultipleConditions(): void
    {
        $lhs = $this->randomCondition();
        $rhs = $this->randomCondition();
        $query = "{$lhs->name} {$lhs->operator} {$lhs->value}"
            . " and {$rhs->name} {$rhs->operator} {$rhs->value}";

        $filter = new Filter($query);

        self::assertSame($query, $filter->query());
        self::assertEquals(
            $this->dumpConditions([
                new Condition($lhs->name, $lhs->operator, $lhs->value),
                new Condition($rhs->name, $rhs->operator, $rhs->value),
            ]),
            $this->dumpConditions($filter->conditions())
        );
    }

    /**
     * @param Condition[] $conditions
     * @return DumpedCondition[]
     */
    private function dumpConditions(array $conditions): array
    {
        return array_map(
            fn (Condition $condition) => $this->dumpCondition($condition),
            $conditions
        );
    }

    /**
     * Condition id is always different so we need a function to dump its (relevant) values.
     *
     * @return DumpedCondition
     */
    private function dumpCondition(Condition $condition): array
    {
        return [
            'name' => $condition->name(),
            'operator' => $condition->operator(),
            'value' => $condition->value(),
        ];
    }

    private function randomCondition(): \stdClass
    {
        $condition = new \stdClass();
        $condition->name = $this->randomName();
        $condition->operator = $this->randomOperator();
        $condition->value = $this->randomValue();

        return $condition;
    }

    private function randomName(): string
    {
        return md5(random_bytes(16));
    }

    private function randomOperator(): string
    {
        $candidates = [
            Operator::OP_EQ,
            Operator::OP_NEQ,
            Operator::OP_LIKE,
            Operator::OP_NLIKE,
            Operator::OP_GT,
            Operator::OP_GTE,
            Operator::OP_LT,
            Operator::OP_LTE,
        ];

        return $candidates[array_rand($candidates)];
    }

    private function randomValue(): string
    {
        return md5(random_bytes(16));
    }
}
