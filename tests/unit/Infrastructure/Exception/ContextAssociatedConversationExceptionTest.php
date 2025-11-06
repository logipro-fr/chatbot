<?php

namespace Chatbot\Tests\Infrastructure\Exception;

use Chatbot\Infrastructure\Exception\ContextAssociatedConversationException;
use PHPUnit\Framework\TestCase;

class ContextAssociatedConversationExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(ContextAssociatedConversationException::class);

        throw new ContextAssociatedConversationException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(ContextAssociatedConversationException::class);
        $this->expectExceptionMessage($message);

        throw new ContextAssociatedConversationException($message);
    }
}
