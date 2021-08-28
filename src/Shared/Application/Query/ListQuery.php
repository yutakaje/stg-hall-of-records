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

namespace Stg\HallOfRecords\Shared\Application\Query;

use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

final class ListQuery extends AbstractQuery
{
    private Filter $filter;

    public function __construct(
        Locale $locale,
        ?Filter $filter = null
    ) {
        parent::__construct($locale);
        $this->filter = $filter ?? new Filter();
    }

    public function filter(): Filter
    {
        return $this->filter;
    }
}
