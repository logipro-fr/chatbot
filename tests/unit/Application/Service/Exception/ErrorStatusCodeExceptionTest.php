<?php

namespace Chatbot\Tests\Application\Service\Exception;

use Chatbot\Application\Service\Exception\ErrorStatusCodeException;
use PHPUnit\Framework\TestCase;

class ErrorStatusCodeExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(ErrorStatusCodeException::class);

        throw new ErrorStatusCodeException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(ErrorStatusCodeException::class);
        $this->expectExceptionMessage($message);

        throw new ErrorStatusCodeException($message);
    }
}
