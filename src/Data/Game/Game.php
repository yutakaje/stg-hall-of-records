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

namespace Stg\HallOfRecords\Data\Game;

use Stg\HallOfRecords\Data\Sorting\SortableInterface;

final class Game implements SortableInterface
{
    private int $id;
    private string $name;
    private string $company;

    public function __construct(
        int $id,
        string $name,
        string $company
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->company = $company;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function company(): string
    {
        return $this->company;
    }

    /**
     * @return mixed
     */
    public function getProperty(string $name)
    {
        switch ($name) {
            case 'id':
                return $this->id;
            case 'name':
                return $this->name;
            case 'company':
                return $this->company;
            default:
                return null;
        }
    }
}
