<?php

namespace Chatbot\Tests\Application\Service\Exception;

use Chatbot\Application\Service\Exception\OtherException;
use PHPUnit\Framework\TestCase;

class OtherExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(OtherException::class);

        throw new OtherException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(OtherException::class);
        $this->expectExceptionMessage($message);

        throw new OtherException($message);
    }
}
