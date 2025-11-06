<?php

namespace Chatbot\Tests\Application\Service\Exception;

use Chatbot\Application\Service\Exception\BadInstanceException;
use PHPUnit\Framework\TestCase;

class BadInstanceExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(BadInstanceException::class);

        throw new BadInstanceException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(BadInstanceException::class);
        $this->expectExceptionMessage($message);

        throw new BadInstanceException($message);
    }
}
