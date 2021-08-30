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

        $appliedQb = $column->apply($qb, $condition);

        $parameterNames = array_keys($appliedQb->getParameters());
        self::assertCount(1, $parameterNames);

        $expectedOperator = $this->mapOperator($operator);
        $expectedQb = $this->db()->fakeConnection()->createQueryBuilder()
            ->where("{$columnName} {$expectedOperator} :{$parameterNames[0]}")
            ->setParameter($parameterNames[0], $value, ParameterType::INTEGER);

        self::assertSame($expectedQb->getSQL(), $appliedQb->getSQL());
        self::assertSame($expectedQb->getParameters(), $appliedQb->getParameters());
        self::assertSame($expectedQb->getParameterTypes(), $appliedQb->getParameterTypes());
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

        $appliedQb = $column->apply($qb, $condition);

        $parameterNames = array_keys($appliedQb->getParameters());
        self::assertCount(1, $parameterNames);

        $expectedOperator = $this->mapOperator($operator);
        $expectedQb = $this->db()->fakeConnection()->createQueryBuilder()
            ->where("LOWER({$columnName}) {$expectedOperator} LOWER(:{$parameterNames[0]})")
            ->setParameter($parameterNames[0], $expectedValue, ParameterType::STRING);

        self::assertSame($expectedQb->getSQL(), $appliedQb->getSQL());
        self::assertSame($expectedQb->getParameters(), $appliedQb->getParameters());
        self::assertSame($expectedQb->getParameterTypes(), $appliedQb->getParameterTypes());
    }

    public function testOneOfColumnWithInt(): void
    {
        $columnNames = [
            $this->randomColumnName(),
            $this->randomColumnName(),
        ];
        $passedValue = $this->randomValue();
        $expectedValue = $passedValue;

        $condition = new Condition(
            $this->randomName(),
            Operator::OP_GT,
            $passedValue
        );

        $qb = $this->db()->fakeConnection()->createQueryBuilder();

        $column = QueryColumn::oneOf(
            QueryColumn::int($columnNames[0]),
            QueryColumn::int($columnNames[1]),
        );

        $appliedQb = $column->apply($qb, $condition);

        $parameterNames = array_keys($appliedQb->getParameters());
        self::assertCount(2, $parameterNames);

        $expectedOperator = $this->mapOperator($condition->operator()->value());
        $expectedQb = $this->db()->fakeConnection()->createQueryBuilder()
            ->where(
                "{$columnNames[0]} {$expectedOperator} :{$parameterNames[0]}"
                . " OR {$columnNames[1]} {$expectedOperator} :{$parameterNames[1]}"
            )
            ->setParameter($parameterNames[0], $expectedValue, ParameterType::INTEGER)
            ->setParameter($parameterNames[1], $expectedValue, ParameterType::INTEGER);

        self::assertSame($expectedQb->getSQL(), $appliedQb->getSQL());
        self::assertSame($expectedQb->getParameters(), $appliedQb->getParameters());
        self::assertSame($expectedQb->getParameterTypes(), $appliedQb->getParameterTypes());
    }

    public function testOneOfColumnWithStringNonFuzzy(): void
    {
        $passedValue = $this->randomValue();
        $expectedValue = $passedValue;

        $this->testOneOfColumnWithString(Operator::OP_EQ, $passedValue, $expectedValue);
    }

    public function testOneOfColumnWithStringFuzzy(): void
    {
        $passedValue = $this->randomValue();
        $expectedValue = "%{$passedValue}%";

        $this->testOneOfColumnWithString(Operator::OP_LIKE, $passedValue, $expectedValue);
    }

    private function testOneOfColumnWithString(
        string $operator,
        string $passedValue,
        string $expectedValue
    ): void {
        $columnNames = [
            $this->randomColumnName(),
            $this->randomColumnName(),
        ];

        $condition = new Condition(
            $this->randomName(),
            $operator,
            $passedValue
        );

        $qb = $this->db()->fakeConnection()->createQueryBuilder();

        $column = QueryColumn::oneOf(
            QueryColumn::string($columnNames[0]),
            QueryColumn::string($columnNames[1]),
        );

        $appliedQb = $column->apply($qb, $condition);

        $parameterNames = array_keys($appliedQb->getParameters());
        self::assertCount(2, $parameterNames);

        $expectedOperator = $this->mapOperator($condition->operator()->value());
        $expectedQb = $this->db()->fakeConnection()->createQueryBuilder()
            ->where(
                "LOWER({$columnNames[0]}) {$expectedOperator} LOWER(:{$parameterNames[0]})"
                . " OR LOWER({$columnNames[1]}) {$expectedOperator} LOWER(:{$parameterNames[1]})"
            )
            ->setParameter($parameterNames[0], $expectedValue, ParameterType::STRING)
            ->setParameter($parameterNames[1], $expectedValue, ParameterType::STRING);

        self::assertSame($expectedQb->getSQL(), $appliedQb->getSQL());
        self::assertSame($expectedQb->getParameters(), $appliedQb->getParameters());
        self::assertSame($expectedQb->getParameterTypes(), $appliedQb->getParameterTypes());
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
