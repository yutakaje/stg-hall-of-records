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

namespace Stg\HallOfRecords\Scrap;

final class MessageHandler
{
    /** @var Message[] */
    private array $messages;
    /** @var array<string,mixed> */
    private array $context;

    public function __construct()
    {
        $this->messages = [];
        $this->context = [];
    }

    /**
     * @return Message[]
     */
    public function all(): array
    {
        return $this->messages;
    }

    /**
     * @param array<string,mixed> $context
     */
    public function addMessage(string $message, array $context = []): void
    {
        $this->messages[] = new Message(
            $message,
            array_merge($this->context, $context)
        );
    }

    /**
     * @param mixed $value
     */
    public function addContext(string $name, $value): void
    {
        $this->context[$name] = $value;
    }

    public function removeContext(string $name): void
    {
        unset($this->context[$name]);
    }

    public function reset(): void
    {
        $this->messages = [];
        $this->context = [];
    }

    /**
     * @param Message[] $messages
     * @return Message[]
     */
    public function filterEmptyBlocks(
        array $messages,
        string $firstMessageOfBlock,
        string $lastMessageOfBlock
    ): array {
        for ($i = 0; $i < sizeof($messages); ++$i) {
            $current = $messages[$i];
            $next = $messages[$i + 1] ?? null;

            if (
                $next !== null
                && $current->message() === $firstMessageOfBlock
                && $next->message() === $lastMessageOfBlock
            ) {
                //$messages = array_splice($messages, $i, 2);
                unset($messages[$i]);
                unset($messages[$i + 1]);
                $messages = array_values($messages);
                $i = $i - 1;
            }
        }

        return $messages;
    }
}
