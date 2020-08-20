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

namespace Stg\HallOfRecords\Import;

final class ParsedScore extends AbstractParsedObject
{
    private int $id;

    /**
     * @param array<string,mixed> $properties
     */
    public function __construct(
        int $id,
        array $properties
    ) {
        parent::__construct($properties);
        $this->id = $id;
    }

    public function id(): int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getProperty(string $name)
    {
        switch ($name) {
            case 'id':
                return $this->id;
            default:
                return parent::getProperty($name);
        }
    }
}
