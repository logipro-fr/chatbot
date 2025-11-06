<?php

namespace Chatbot\Tests\Infrastructure\Exception;

use Chatbot\Infrastructure\Exception\AssistantNotFoundException;
use PHPUnit\Framework\TestCase;

class AssistantNotFoundExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(AssistantNotFoundException::class);

        throw new AssistantNotFoundException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(AssistantNotFoundException::class);
        $this->expectExceptionMessage($message);

        throw new AssistantNotFoundException($message);
    }
}
