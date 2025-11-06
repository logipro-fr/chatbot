<?php

namespace Chatbot\Tests\Application\Service\Exception;

use Chatbot\Application\Service\Exception\TooManyRequestException;
use PHPUnit\Framework\TestCase;

class TooManyRequestExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(TooManyRequestException::class);

        throw new TooManyRequestException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(TooManyRequestException::class);
        $this->expectExceptionMessage($message);

        throw new TooManyRequestException($message);
    }
}
