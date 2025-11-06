<?php

namespace Chatbot\Tests\Infrastructure\Exception;

use Chatbot\Infrastructure\Exception\ConversationNotFoundException;
use PHPUnit\Framework\TestCase;

class ConversationNotFoundExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(ConversationNotFoundException::class);

        throw new ConversationNotFoundException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(ConversationNotFoundException::class);
        $this->expectExceptionMessage($message);

        throw new ConversationNotFoundException($message);
    }
}
