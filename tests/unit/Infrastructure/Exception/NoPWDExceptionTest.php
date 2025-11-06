<?php

namespace Chatbot\Tests\Infrastructure\Exception;

use Chatbot\Infrastructure\Exception\NoPWDException;
use PHPUnit\Framework\TestCase;

class NoPWDExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(NoPWDException::class);

        throw new NoPWDException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(NoPWDException::class);
        $this->expectExceptionMessage($message);

        throw new NoPWDException($message);
    }
}
