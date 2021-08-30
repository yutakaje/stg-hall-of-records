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
use Stg\HallOfRecords\Shared\Infrastructure\Database\ParameterMerger;

class ParameterMergerTest extends \Tests\TestCase
{
    public function testWithGoodQbs(): void
    {
        $connection = $this->db()->fakeConnection();
        $expr = $connection->createQueryBuilder()->expr();

        $columnNames = [
            $this->randomColumnName(),
            $this->randomColumnName(),
        ];
        $parameterNames = [
            $this->randomParameterName(),
            $this->randomParameterName(),
            $this->randomParameterName(),
            $this->randomParameterName(),
        ];
        $values = [
            $this->randomValue(),
            $this->randomValue(),
            $this->randomValue(),
            $this->randomValue(),
        ];

        $main = $connection->createQueryBuilder()
            ->select('column1', 'column2')
            ->from('some_table')
            ->where($expr->eq($columnNames[0], $parameterNames[0]))
            ->setParameter($parameterNames[0], $values[0], ParameterType::STRING);

        $qbs = [
            $connection->createQueryBuilder()
                ->setParameter($parameterNames[1], $values[1], ParameterType::INTEGER)
                ->setParameter($parameterNames[2], $values[2], ParameterType::STRING),
            $connection->createQueryBuilder()
                ->setParameter($parameterNames[3], $values[3], ParameterType::STRING),
        ];

        $merger = new ParameterMerger();

        self::assertEquals(
            $connection->createQueryBuilder()
                ->select('column1', 'column2')
                ->from('some_table')
                ->where($expr->eq($columnNames[0], $parameterNames[0]))
                ->setParameter($parameterNames[0], $values[0], ParameterType::STRING)
                ->setParameter($parameterNames[1], $values[1], ParameterType::INTEGER)
                ->setParameter($parameterNames[2], $values[2], ParameterType::STRING)
                ->setParameter($parameterNames[3], $values[3], ParameterType::STRING),
            $merger->merge($main, ...$qbs)
        );
    }

    public function testWithParameterLossage(): void
    {
        $connection = $this->db()->fakeConnection();
        $expr = $connection->createQueryBuilder()->expr();

        $columnNames = [
            $this->randomColumnName(),
            $this->randomColumnName(),
        ];
        $parameterNames = [
            $this->randomParameterName(),
            $this->randomParameterName(),
            $this->randomParameterName(),
            $this->randomParameterName(),
        ];
        $values = [
            $this->randomValue(),
            $this->randomValue(),
            $this->randomValue(),
            $this->randomValue(),
        ];

        $main = $connection->createQueryBuilder()
            ->select('column1', 'column2')
            ->from('some_table')
            ->where($expr->eq($columnNames[0], $parameterNames[0]))
            ->setParameter($parameterNames[0], $values[0], ParameterType::STRING);

        $qbs = [
            $connection->createQueryBuilder()
                ->setParameter($parameterNames[1], $values[1], ParameterType::INTEGER)
                ->setParameter($parameterNames[2], $values[2], ParameterType::STRING),
            $connection->createQueryBuilder()
                ->setParameter($parameterNames[1], $values[1], ParameterType::INTEGER)
                ->setParameter($parameterNames[3], $values[3], ParameterType::STRING),
        ];

        $merger = new ParameterMerger();

        try {
            $merger->merge($main, ...$qbs);
            self::fail('Call to `merge` should throw an exception.');
        } catch (\InvalidArgumentException $exception) {
            self::succeed();
        }
    }

    private function randomColumnName(): string
    {
        return md5(random_bytes(16));
    }

    private function randomParameterName(): string
    {
        return md5(random_bytes(16));
    }

    private function randomValue(): string
    {
        return md5(random_bytes(16));
    }
}
