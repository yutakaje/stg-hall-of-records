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

namespace Stg\HallOfRecords\Database\Definition;

use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

/**
 * @phpstan-type Source array{name:string, date:string, url:string}
 * @phpstan-type Sources Source[]
 * @phpstan-type LocalizedSources array<string,Sources>
 * @phpstan-type Attributes ScoreAttributeRecord[]
 */
final class ScoreRecord extends AbstractRecord
{
    private int $gameId;
    private ?int $playerId;
    private string $playerName;
    private string $scoreValue;
    private string $realScoreValue;
    private string $sortScoreValue;
    /** @var LocalizedSources */
    private array $sources;
    /** @var Attributes */
    private array $attributes;

    /**
     * @param LocalizedSources $sources
     * @param Attributes $attributes
     */
    public function __construct(
        int $gameId,
        ?int $playerId,
        string $playerName,
        string $scoreValue,
        string $realScoreValue,
        string $sortScoreValue,
        array $sources,
        array $attributes
    ) {
        parent::__construct();
        $this->gameId = $gameId;
        $this->playerId = $playerId;
        $this->playerName = $playerName;
        $this->scoreValue = $scoreValue;
        $this->realScoreValue = $realScoreValue;
        $this->sortScoreValue = $sortScoreValue;
        $this->sources = $sources;
        $this->attributes = $attributes;
    }

    public function gameId(): int
    {
        return $this->gameId;
    }

    public function playerId(): ?int
    {
        return $this->playerId;
    }

    public function playerName(): string
    {
        return $this->playerName;
    }

    public function scoreValue(): string
    {
        return $this->scoreValue;
    }

    public function realScoreValue(): string
    {
        return $this->realScoreValue;
    }

    public function sortScoreValue(): string
    {
        return $this->sortScoreValue;
    }

    /**
     * @return Sources
     */
    public function sources(Locale $locale): array
    {
        return $this->localizedValue($this->sources, $locale);
    }

    /**
     * @return Attributes
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
