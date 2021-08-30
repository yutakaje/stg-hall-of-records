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
use Stg\HallOfRecords\Shared\Infrastructure\Database\Comparison\OneOfComparison;
use Stg\HallOfRecords\Shared\Infrastructure\Database\Comparison\StringComparison;

/**
 * @phpstan-type DataType self::*
 * @phpstan-type Parameters array<string,mixed>
 */
final class QueryColumn
{
    private const INT = 'int';
    private const STRING = 'string';
    private const ONE_OF = 'oneOf';

    /** @var DataType */
    private string $dataType;
    /** @var Parameters */
    private array $parameters;

    /**
     * @param DataType $dataType
     * @param Parameters $parameters
     */
    private function __construct(
        string $dataType,
        array $parameters
    ) {
        $this->dataType = $dataType;
        $this->parameters = $parameters;
    }

    public static function int(string $columnName): self
    {
        return new self(self::INT, [
            'columnName' => $columnName,
        ]);
    }

    public static function string(string $columnName): self
    {
        return new self(self::STRING, [
            'columnName' => $columnName,
        ]);
    }

    public static function oneOf(QueryColumn ...$columns): self
    {
        return new self(self::ONE_OF, [
            'columns' => $columns,
        ]);
    }

    public function apply(QueryBuilder $qb, Condition $condition): QueryBuilder
    {
        return $this->createComparison($condition)->applyTo($qb);
    }

    private function createComparison(Condition $condition): ComparisonInterface
    {
        switch ($this->dataType) {
            case self::INT:
                return new IntComparison(
                    $condition->id(),
                    $this->columnName(),
                    $condition->operator(),
                    $condition->value()
                );

            case self::STRING:
                return new StringComparison(
                    $condition->id(),
                    $this->columnName(),
                    $condition->operator(),
                    $condition->value()
                );

            case self::ONE_OF:
                return new OneOfComparison(array_map(
                    // We create a new condition for each comparison because
                    // we do not want them to share the same condition id.
                    fn (QueryColumn $column) => $column->createComparison(
                        new Condition(
                            $condition->name(),
                            $condition->operator()->value(),
                            $condition->value()
                        )
                    ),
                    $this->columns()
                ));

            default:
                throw new \LogicException(
                    "Invalid data type `{$this->dataType}`"
                );
        }
    }

    private function columnName(): string
    {
        return $this->parameters['columnName'];
    }

    /**
     * @return QueryColumn[]
     */
    private function columns(): array
    {
        return $this->parameters['columns'];
    }
}
