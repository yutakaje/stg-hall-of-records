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

final class ViewQuery extends AbstractQuery
{
    private string $id;

    public function __construct(string $id, string $locale)
    {
        parent::__construct($locale);
        $this->id = $id;
    }

    public function id(): string
    {
        return $this->id;
    }
}
