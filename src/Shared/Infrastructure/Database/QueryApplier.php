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
use Stg\HallOfRecords\Shared\Application\Query\Filter;
use Stg\HallOfRecords\Shared\Application\Query\Filter\Condition;
use Stg\HallOfRecords\Shared\Application\Query\Filter\FilterException;

/**
 * @phpstan-type FieldName string
 */
final class QueryApplier
{
    /** @var array<FieldName,QueryColumn> */
    private array $columns;
    private ?FilterException $exception;

    /**
     * @param array<FieldName,QueryColumn> $columns
     */
    public function __construct(array $columns)
    {
        $this->columns = $columns;
        $this->exception = null;
    }

    public function containsError(): bool
    {
        return $this->exception !== null;
    }

    public function errorMessage(): string
    {
        if ($this->exception === null) {
            throw new \LogicException(
                'Function may only be called in case of an error,'
                . ' use `containsError` to check'
            );
        }

        return $this->exception->getMessage();
    }

    public function applyFilter(QueryBuilder $qb, Filter $filter): QueryBuilder
    {
        $this->exception = null;

        try {
            return array_reduce(
                $filter->conditions(),
                fn (
                    QueryBuilder $qb,
                    Condition $condition
                ) => $this->applyCondition($qb, $condition),
                $qb
            );
        } catch (FilterException $exception) {
            // Caller can decide whether he wants to throw the exception
            // in case the filter could not be applied.
            $this->exception = $exception;
            return $qb;
        }
    }

    private function applyCondition(
        QueryBuilder $qb,
        Condition $condition
    ): QueryBuilder {
        $column = $this->column($condition->name());

        return $column->apply($qb, $condition);
    }

    private function column(string $name): QueryColumn
    {
        $column = $this->columns[$name] ?? null;

        if ($column === null) {
            throw FilterException::invalidFieldName($name);
        }

        return $column;
    }
}
