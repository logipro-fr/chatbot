<?php

namespace Chatbot\Tests\Application\Service\Exception;

use Chatbot\Application\Service\Exception\BadTypeNameException;
use PHPUnit\Framework\TestCase;

class BadTypeNameExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(BadTypeNameException::class);

        throw new BadTypeNameException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(BadTypeNameException::class);
        $this->expectExceptionMessage($message);

        throw new BadTypeNameException($message);
    }
}
