<?php

namespace Chatbot\Tests\Application\Service\Exception;

use Chatbot\Application\Service\Exception\EmptyStringException;
use PHPUnit\Framework\TestCase;

class EmptyStringExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(EmptyStringException::class);

        throw new EmptyStringException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(EmptyStringException::class);
        $this->expectExceptionMessage($message);

        throw new EmptyStringException($message);
    }
}
