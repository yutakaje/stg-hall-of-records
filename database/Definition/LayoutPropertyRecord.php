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
 * @phpstan-type Value mixed
 */
final class LayoutPropertyRecord extends AbstractRecord
{
    private ?int $gameId;
    private string $name;
    /** @var Value */
    private $value;
    private ?Locale $locale;

    /**
     * @param Value $value
     */
    public function __construct(
        ?int $gameId,
        string $name,
        $value,
        ?Locale $locale
    ) {
        parent::__construct();
        $this->gameId = $gameId;
        $this->name = $name;
        $this->value = $value;
        $this->locale = $locale;
    }

    public function gameId(): ?int
    {
        return $this->gameId;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return Value
     */
    public function value()
    {
        return $this->value;
    }

    public function locale(): ?Locale
    {
        return $this->locale;
    }
}
