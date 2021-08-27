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

namespace Tests\HallOfRecords\Shared\Infrastructure\Database;

use Doctrine\DBAL\ParameterType;
use Stg\HallOfRecords\Shared\Infrastructure\Database\QueryColumn;
use Stg\HallOfRecords\Shared\Application\Query\Filter\Condition;
use Stg\HallOfRecords\Shared\Application\Query\Filter\Operator;

class QueryColumnTest extends \Tests\TestCase
{
    public function testIntColumnEquals(): void
    {
        $this->testIntColumn(Operator::OP_EQ);
    }

    public function testIntColumnNotEquals(): void
    {
        $this->testIntColumn(Operator::OP_NEQ);
    }

    public function testIntColumnGreaterThan(): void
    {
        $this->testIntColumn(Operator::OP_GT);
    }

    public function testIntColumnGreaterThanOrEquals(): void
    {
        $this->testIntColumn(Operator::OP_GTE);
    }

    public function testIntColumnLessThan(): void
    {
        $this->testIntColumn(Operator::OP_LT);
    }

    public function testIntColumnLessThanOrEquals(): void
    {
        $this->testIntColumn(Operator::OP_LTE);
    }

    private function testIntColumn(string $operator): void
    {
        $columnName = $this->randomColumnName();
        $value = $this->randomValue();

        $condition = new Condition(
            $this->randomName(),
            $operator,
            $value
        );

        $qb = $this->db()->fakeConnection()->createQueryBuilder();

        $column = QueryColumn::int($columnName);

        self::assertEquals(
            $this->db()->fakeConnection()->createQueryBuilder()
                ->where("{$columnName} {$this->mapOperator($operator)} :{$condition->id()}")
                ->setParameter($condition->id(), $value, ParameterType::INTEGER),
            $column->apply($qb, $condition)
        );
    }

    public function testStringColumnEquals(): void
    {
        $passedValue = $this->randomValue();
        $expectedValue = $passedValue;

        $this->testStringColumn(Operator::OP_EQ, $passedValue, $expectedValue);
    }

    public function testStringColumnNotEquals(): void
    {
        $passedValue = $this->randomValue();
        $expectedValue = $passedValue;

        $this->testStringColumn(Operator::OP_NEQ, $passedValue, $expectedValue);
    }

    public function testStringColumnLike(): void
    {
        $passedValue = $this->randomValue();
        $expectedValue = "%{$passedValue}%";

        $this->testStringColumn(Operator::OP_LIKE, $passedValue, $expectedValue);
    }

    public function testStringColumnNotLike(): void
    {
        $passedValue = $this->randomValue();
        $expectedValue = "%{$passedValue}%";

        $this->testStringColumn(Operator::OP_NLIKE, $passedValue, $expectedValue);
    }

    private function testStringColumn(
        string $operator,
        string $passedValue,
        string $expectedValue
    ): void {
        $columnName = $this->randomColumnName();

        $condition = new Condition(
            $this->randomName(),
            $operator,
            $passedValue
        );

        $qb = $this->db()->fakeConnection()->createQueryBuilder();

        $column = QueryColumn::string($columnName);

        $expectedOperator = $this->mapOperator($operator);
        self::assertEquals(
            $this->db()->fakeConnection()->createQueryBuilder()
                ->where("LOWER({$columnName}) {$expectedOperator} LOWER(:{$condition->id()})")
                ->setParameter($condition->id(), $expectedValue, ParameterType::STRING),
            $column->apply($qb, $condition)
        );
    }

    private function randomColumnName(): string
    {
        return md5(random_bytes(16));
    }

    private function randomName(): string
    {
        return md5(random_bytes(16));
    }

    private function randomValue(): string
    {
        return md5(random_bytes(16));
    }

    private function mapOperator(string $operator): string
    {
        $mapping = [
            Operator::OP_EQ => '=',
            Operator::OP_NEQ => '<>',
            Operator::OP_LIKE => 'LIKE',
            Operator::OP_NLIKE => 'NOT LIKE',
            Operator::OP_GT => '>',
            Operator::OP_GTE => '>=',
            Operator::OP_LT => '<',
            Operator::OP_LTE => '<=',
        ];

        return $mapping[$operator] ?? '';
    }
}
