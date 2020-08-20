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

final class ParsedGame extends AbstractParsedObject
{
    private int $id;
    /** @var ParsedScore[] */
    private array $scores;
    private ParsedLayout $layout;

    /**
     * @param array<string,mixed> $properties
     * @param ParsedScore[] $scores
     */
    public function __construct(
        int $id,
        array $properties,
        array $scores,
        ParsedLayout $layout
    ) {
        parent::__construct($properties);
        $this->id = $id;
        $this->scores = $scores;
        $this->layout = $layout;
    }

    public function id(): int
    {
        return $this->id;
    }

    /**
     * @return ParsedScore[]
     */
    public function scores(): array
    {
        return $this->scores;
    }

    public function layout(): ParsedLayout
    {
        return $this->layout;
    }
}
