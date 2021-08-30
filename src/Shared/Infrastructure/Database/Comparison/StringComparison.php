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

namespace Stg\HallOfRecords\Shared\Infrastructure\Database\Comparison;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use Stg\HallOfRecords\Shared\Application\Query\Filter\FilterException;
use Stg\HallOfRecords\Shared\Application\Query\Filter\Operator;
use Stg\HallOfRecords\Shared\Infrastructure\Database\ComparisonInterface;

final class StringComparison implements ComparisonInterface
{
    private string $id;
    private string $columnName;
    private Operator $operator;
    /** @var mixed */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct(
        string $id,
        string $columnName,
        Operator $operator,
        $value
    ) {
        $this->id = $id;
        $this->columnName = $columnName;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function applyTo(QueryBuilder $qb): QueryBuilder
    {
        $placeholder = $this->id;
        $operator = $this->operator->value();

        $qb->setParameter(
            $placeholder,
            $this->getParameterValue($operator),
            ParameterType::STRING
        );

        return $qb->andWhere($this->compare(
            $qb->expr(),
            $this->columnName,
            $operator,
            $placeholder
        ));
    }

    private function compare(
        ExpressionBuilder $expr,
        string $column,
        string $operator,
        string $placeholder
    ): string {
        $column = $this->lower($column);
        $placeholder = $this->lower(":{$placeholder}");

        switch ($operator) {
            case Operator::EQ:
                return $expr->eq($column, $placeholder);

            case Operator::NEQ:
                return $expr->neq($column, $placeholder);

            case Operator::LIKE:
                return $expr->like($column, $placeholder);

            case Operator::NLIKE:
                return $expr->notLike($column, $placeholder);

            case Operator::GT:
            case Operator::GTE:
            case Operator::LT:
            case Operator::LTE:
            default:
                throw FilterException::invalidComparison('string', $operator);
        }
    }

    private function getParameterValue(string $operator): string
    {
        switch ($operator) {
            case Operator::EQ:
            case Operator::NEQ:
                return $this->value;

            case Operator::LIKE:
            case Operator::NLIKE:
                return "%{$this->value}%";

            case Operator::GT:
            case Operator::GTE:
            case Operator::LT:
            case Operator::LTE:
            default:
                throw FilterException::invalidComparison('string', $operator);
        }
    }

    private function lower(string $expression): string
    {
        return "LOWER({$expression})";
    }
}
