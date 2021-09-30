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
 * @phpstan-type LocalizedValues array<string,string>
 */
final class ScoreAttributeRecord extends AbstractRecord
{
    private ?int $scoreId;
    private string $name;
    private string $value;
    /** @var LocalizedValues */
    private array $titles;

    /**
     * @param LocalizedValues $titles
     */
    public function __construct(
        string $name,
        string $value,
        array $titles
    ) {
        parent::__construct();
        $this->scoreId = null;
        $this->name = $name;
        $this->value = $value;
        $this->titles = $titles;
    }

    public function scoreId(): int
    {
        if ($this->scoreId === null) {
            throw new \LogicException('Id has not been set');
        }

        return $this->scoreId;
    }

    public function setScoreId(int $scoreId): void
    {
        if ($this->scoreId !== null) {
            throw new \LogicException('Score id has already been set');
        }

        $this->scoreId = $scoreId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function title(Locale $locale): string
    {
        return $this->localizedValue($this->titles, $locale);
    }
}
