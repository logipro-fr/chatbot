<?php

namespace Chatbot\Tests\Infrastructure\Exception;

use Chatbot\Infrastructure\Exception\ThreadNotFoundException;
use PHPUnit\Framework\TestCase;

class ThreadNotFoundExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(ThreadNotFoundException::class);

        throw new ThreadNotFoundException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(ThreadNotFoundException::class);
        $this->expectExceptionMessage($message);

        throw new ThreadNotFoundException($message);
    }
}
