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

namespace Tests\HallOfRecords\Shared\Application;

use Stg\HallOfRecords\Shared\Application\ResultMessage;

class ResultMessageTest extends \Tests\TestCase
{
    public function testNone(): void
    {
        $message = ResultMessage::none();

        self::assertSame('none', $message->type());
        self::assertEmpty($message->message());
        self::assertEmpty($message->attributes());
    }

    public function testSuccess(): void
    {
        $successMessage = $this->randomMessage();
        $attributes = $this->randomAttributes();

        $message = ResultMessage::success($successMessage, $attributes);

        self::assertSame('success', $message->type());
        self::assertSame($successMessage, $message->message());
        self::assertSame($attributes, $message->attributes());
    }

    public function testWarning(): void
    {
        $warningMessage = $this->randomMessage();
        $attributes = $this->randomAttributes();

        $message = ResultMessage::warning($warningMessage, $attributes);

        self::assertSame('warning', $message->type());
        self::assertSame($warningMessage, $message->message());
        self::assertSame($attributes, $message->attributes());
    }

    public function testError(): void
    {
        $errorMessage = $this->randomMessage();
        $attributes = $this->randomAttributes();

        $message = ResultMessage::error($errorMessage, $attributes);

        self::assertSame('error', $message->type());
        self::assertSame($errorMessage, $message->message());
        self::assertSame($attributes, $message->attributes());
    }

    private function randomMessage(): string
    {
        return md5(random_bytes(32)) . ' ' . md5(random_bytes(32));
    }

    /**
     * @return array<string,string>
     */
    private function randomAttributes(): array
    {
        return [
            md5(random_bytes(8)) => md5(random_bytes(16)),
            md5(random_bytes(8)) => md5(random_bytes(16)),
        ];
    }
}
