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

    /**
     * @param Type $type
     */
    private function __construct(string $type, string $message)
    {
        $this->type = $type;
        $this->message = $message;
    }

    public static function none(): self
    {
        return new self(self::TYPE_NONE, '');
    }

    public static function success(string $message): self
    {
        return new self(self::TYPE_SUCCESS, $message);
    }

    public static function warning(string $message): self
    {
        return new self(self::TYPE_WARNING, $message);
    }

    public static function error(string $message): self
    {
        return new self(self::TYPE_ERROR, $message);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function message(): string
    {
        return $this->message;
    }
}
