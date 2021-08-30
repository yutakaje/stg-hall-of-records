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

use Stg\HallOfRecords\Shared\Application\Query\Filter\FilterException;
use Stg\HallOfRecords\Shared\Application\Query\Filter\Operator;

class OperatorTest extends \Tests\TestCase
{
    public function testEquals(): void
    {
        $this->testWithValidValue(Operator::EQ);
    }

    public function testNotEquals(): void
    {
        $this->testWithValidValue(Operator::NEQ);
    }

    public function testLike(): void
    {
        $this->testWithValidValue(Operator::LIKE);
    }

    public function testNotLike(): void
    {
        $this->testWithValidValue(Operator::NLIKE);
    }

    public function testGreaterThan(): void
    {
        $this->testWithValidValue(Operator::GT);
    }

    public function testGreaterThanOrEquals(): void
    {
        $this->testWithValidValue(Operator::GTE);
    }

    public function testLessThan(): void
    {
        $this->testWithValidValue(Operator::LT);
    }

    public function testLessThanOrEquals(): void
    {
        $this->testWithValidValue(Operator::LTE);
    }

    private function testWithValidValue(string $value): void
    {
        $operator = new Operator($value);

        self::assertSame($value, $operator->value());
    }

    public function testWithInvalidValue(): void
    {
        $value = '&';

        try {
            $operator = new Operator($value);
            self::fail('Call to constructor should throw an exception.');
        } catch (FilterException $exception) {
            self::assertEquals(
                FilterException::invalidOperator($value),
                $exception
            );
        }
    }
}
