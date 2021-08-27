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

namespace Stg\HallOfRecords\Shared\Infrastructure\Database;

use Doctrine\DBAL\Query\QueryBuilder;
use Stg\HallOfRecords\Shared\Application\Query\Filter\Condition;
use Stg\HallOfRecords\Shared\Infrastructure\Database\Comparison\IntComparison;
use Stg\HallOfRecords\Shared\Infrastructure\Database\Comparison\StringComparison;

/**
 * @phpstan-type DataType self::TYPE_*
 */
final class QueryColumn
{
    private const TYPE_INT = 'int';
    private const TYPE_STRING = 'string';

    /** @var DataType */
    private string $dataType;
    private string $columnName;

    /**
     * @param DataType $dataType
     */
    private function __construct(
        string $dataType,
        string $columnName
    ) {
        $this->dataType = $dataType;
        $this->columnName = $columnName;
    }

    public static function int(string $columnName): self
    {
        return new self(self::TYPE_INT, $columnName);
    }

    public static function string(string $columnName): self
    {
        return new self(self::TYPE_STRING, $columnName);
    }

    public function apply(QueryBuilder $qb, Condition $condition): QueryBuilder
    {
        return $this->createComparison($condition)->applyTo($qb);
    }

    private function createComparison(Condition $condition): ComparisonInterface
    {
        switch ($this->dataType) {
            case self::TYPE_INT:
                return new IntComparison(
                    $condition->id(),
                    $this->columnName,
                    $condition->operator(),
                    $condition->value()
                );

            case self::TYPE_STRING:
                return new StringComparison(
                    $condition->id(),
                    $this->columnName,
                    $condition->operator(),
                    $condition->value()
                );

            default:
                throw new \LogicException(
                    "Invalid data type `{$this->dataType}`"
                );
        }
    }
}
