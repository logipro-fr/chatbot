<?php

namespace Chatbot\Tests\Infrastructure\Exception;

use Chatbot\Infrastructure\Exception\ContextNotFoundException;
use PHPUnit\Framework\TestCase;

class ContextNotFoundExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(ContextNotFoundException::class);

        throw new ContextNotFoundException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(ContextNotFoundException::class);
        $this->expectExceptionMessage($message);

        throw new ContextNotFoundException($message);
    }
}
