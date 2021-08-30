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

use Doctrine\DBAL\Query\QueryBuilder;
use Stg\HallOfRecords\Shared\Infrastructure\Database\ComparisonInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Database\ParameterMerger;

final class OneOfComparison implements ComparisonInterface
{
    /** @var ComparisonInterface[] */
    private array $comparisons;
    private ParameterMerger $parameterMerger;

    /**
     * @param ComparisonInterface[] $comparisons
     */
    public function __construct(array $comparisons)
    {
        $this->comparisons = $comparisons;
        $this->parameterMerger = new ParameterMerger();
    }

    public function applyTo(QueryBuilder $qb): QueryBuilder
    {
        return $this->applyToWrapper($qb, array_map(
            fn (ComparisonInterface $comparison) => $comparison->applyTo(
                $qb->getConnection()->createQueryBuilder()
            ),
            $this->comparisons
        ));
    }

    /**
     * @param QueryBuilder[] $qbs
     */
    private function applyToWrapper(QueryBuilder $wrapper, array $qbs): QueryBuilder
    {
        $this->parameterMerger->merge($wrapper, ...$qbs);

        return $wrapper->andWhere(implode(' OR ', array_map(
            fn (QueryBuilder $qb) => (string)$qb->getQueryPart('where'),
            $qbs
        )));
    }
}
