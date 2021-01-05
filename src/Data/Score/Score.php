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

namespace Stg\HallOfRecords\Data\Score;

use Stg\HallOfRecords\Data\AbstractItem;

final class Score extends AbstractItem
{
    private int $id;
    private int $gameId;
    private bool $isCurrentRecord;

    /**
     * @param array<string,mixed> $properties
     */
    public function __construct(
        int $id,
        int $gameId,
        array $properties = []
    ) {
        parent::__construct($properties);
        $this->id = $id;
        $this->gameId = $gameId;
        $this->isCurrentRecord = ($properties['attributes']['is-current-record'] ?? false) === true;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function gameId(): int
    {
        return $this->gameId;
    }

    public function markAsCurrentRecord(): void
    {
        $this->isCurrentRecord = true;
    }

    /**
     * @return array<string,mixed>
     */
    public function properties(): array
    {
        return array_merge(parent::properties(), [
            'id' => $this->id,
            'game-id' => $this->gameId,
            'is-current-record' => $this->isCurrentRecord,
        ]);
    }
}
