<?php

namespace Chatbot\Tests\Application\Service\Exception;

use Chatbot\Application\Service\Exception\MissingChatbotKeyApiException;
use PHPUnit\Framework\TestCase;

class MissingChatbotKeyApiExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(MissingChatbotKeyApiException::class);

        throw new MissingChatbotKeyApiException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(MissingChatbotKeyApiException::class);
        $this->expectExceptionMessage($message);

        throw new MissingChatbotKeyApiException($message);
    }
}
