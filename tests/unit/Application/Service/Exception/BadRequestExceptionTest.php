<?php

namespace Chatbot\Tests\Application\Service\Exception;

use Chatbot\Application\Service\Exception\BadRequestException;
use PHPUnit\Framework\TestCase;

class BadRequestExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(BadRequestException::class);

        throw new BadRequestException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage($message);

        throw new BadRequestException($message);
    }
}
