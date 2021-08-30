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

namespace Tests\HallOfRecords\Shared\Application\Query\Filter;

use Stg\HallOfRecords\Shared\Application\Query\Filter\Condition;
use Stg\HallOfRecords\Shared\Application\Query\Filter\Operator;

class ConditionTest extends \Tests\TestCase
{
    public function testWithNormalizedValue(): void
    {
        $name = $this->randomName();
        $operator = $this->randomOperator();
        $value = $this->randomValue();

        $condition = new Condition($name, $operator, $value);
        $double = new Condition($name, $operator, $value);

        self::assertSame($name, $condition->name());
        self::assertSame($operator, $condition->operator()->value());
        self::assertSame($value, $condition->value());

        self::assertNotEquals($condition->id(), $double->id());
        self::assertSame($condition->name(), $double->name());
        self::assertEquals($condition->operator(), $double->operator());
        self::assertSame($condition->value(), $double->value());
    }

    public function testWithUnnormalizedValue(): void
    {
        $name = $this->randomName();
        $operator = $this->randomOperator();
        $value = $this->randomValue();

        $condition = new Condition($name, $operator, '"' . $value . '"');

        self::assertSame($name, $condition->name());
        self::assertSame($operator, $condition->operator()->value());
        self::assertSame($value, $condition->value());
    }

    private function randomName(): string
    {
        return md5(random_bytes(16));
    }

    private function randomOperator(): string
    {
        $candidates = [
            Operator::EQ,
            Operator::NEQ,
            Operator::LIKE,
            Operator::NLIKE,
            Operator::GT,
            Operator::GTE,
            Operator::LT,
            Operator::LTE,
        ];

        return $candidates[array_rand($candidates)];
    }

    private function randomValue(): string
    {
        return md5(random_bytes(16));
    }
}
