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
    }

    public function testSuccess(): void
    {
        $successMessage = $this->randomMessage();

        $message = ResultMessage::success($successMessage);

        self::assertSame('success', $message->type());
        self::assertSame($successMessage, $message->message());
    }

    public function testWarning(): void
    {
        $warningMessage = $this->randomMessage();

        $message = ResultMessage::warning($warningMessage);

        self::assertSame('warning', $message->type());
        self::assertSame($warningMessage, $message->message());
    }

    public function testError(): void
    {
        $errorMessage = $this->randomMessage();

        $message = ResultMessage::error($errorMessage);

        self::assertSame('error', $message->type());
        self::assertSame($errorMessage, $message->message());
    }

    private function randomMessage(): string
    {
        return md5(random_bytes(32)) . ' ' . md5(random_bytes(32));
    }
}
