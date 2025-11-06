<?php

namespace Chatbot\Tests\Application\Service\Exception;

use Chatbot\Application\Service\Exception\UnhautorizeKeyException;
use PHPUnit\Framework\TestCase;

class UnhautorizeKeyExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(UnhautorizeKeyException::class);

        throw new UnhautorizeKeyException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(UnhautorizeKeyException::class);
        $this->expectExceptionMessage($message);

        throw new UnhautorizeKeyException($message);
    }
}
