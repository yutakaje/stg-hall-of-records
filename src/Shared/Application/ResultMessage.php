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

namespace Stg\HallOfRecords\Shared\Application;

/**
 * @phpstan-type Type self::TYPE_*
 * @phpstan-type Attributes array<string,string>
 */
final class ResultMessage
{
    private const TYPE_NONE = 'none';
    private const TYPE_SUCCESS = 'success';
    private const TYPE_WARNING = 'warning';
    private const TYPE_ERROR = 'error';

    /** @var Type */
    private string $type;
    private string $message;
    /** @var Attributes */
    private array $attributes;

    /**
     * @param Type $type
     * @param Attributes $attributes
     */
    private function __construct(
        string $type,
        string $message = '',
        array $attributes = []
    ) {
        $this->type = $type;
        $this->message = $message;
        $this->attributes = $attributes;
    }

    public static function none(): self
    {
        return new self(self::TYPE_NONE);
    }

    /**
     * @param Attributes $attributes
     */
    public static function success(string $message, array $attributes = []): self
    {
        return new self(self::TYPE_SUCCESS, $message, $attributes);
    }

    /**
     * @param Attributes $attributes
     */
    public static function warning(string $message, array $attributes = []): self
    {
        return new self(self::TYPE_WARNING, $message, $attributes);
    }

    /**
     * @param Attributes $attributes
     */
    public static function error(string $message, array $attributes = []): self
    {
        return new self(self::TYPE_ERROR, $message, $attributes);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return Attributes
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
