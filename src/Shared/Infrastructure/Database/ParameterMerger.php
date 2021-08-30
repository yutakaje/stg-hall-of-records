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

final class ParameterMerger
{
    public function merge(QueryBuilder $main, QueryBuilder ...$qbs): QueryBuilder
    {
        return array_reduce(
            $qbs,
            fn ($main, $qb) => $this->mergeQueryBuilders($main, $qb),
            $main
        );
    }

    public function mergeQueryBuilders(
        QueryBuilder $lhs,
        QueryBuilder $rhs
    ): QueryBuilder {
        $numParameters = sizeof($lhs->getParameters())
            + sizeof($rhs->getParameters());
        $numParameterTypes = sizeof($lhs->getParameterTypes())
            + sizeof($rhs->getParameterTypes());

        $lhs->setParameters(
            array_merge(
                $lhs->getParameters(),
                $rhs->getParameters()
            ),
            array_merge(
                $lhs->getParameterTypes(),
                $rhs->getParameterTypes()
            )
        );

        if (
            sizeof($lhs->getParameters()) !== $numParameters
            || sizeof($lhs->getParameterTypes()) !== $numParameterTypes
        ) {
            throw new \InvalidArgumentException(
                'Unable to merge two query builders due to parameter loss'
            );
        }

        return $lhs;
    }
}
