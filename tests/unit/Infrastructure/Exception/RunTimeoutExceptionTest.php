<?php

namespace Chatbot\Tests\Infrastructure\Exception;

use Chatbot\Application\Service\Exception\RunTimeoutException;
use PHPUnit\Framework\TestCase;

class RunTimeoutExceptionTest extends TestCase
{
    public function testRunTimeoutExceptionWithDefaultMessage(): void
    {
        $exception = new RunTimeoutException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals("Timeout: Le run n'a pas été complété dans les temps", $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testRunTimeoutExceptionWithCustomMessage(): void
    {
        $customMessage = "Custom timeout message";
        $exception = new RunTimeoutException($customMessage);

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals($customMessage, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testRunTimeoutExceptionWithCustomCode(): void
    {
        $customCode = 500;
        $exception = new RunTimeoutException("Test message", $customCode);

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals("Test message", $exception->getMessage());
        $this->assertEquals($customCode, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testRunTimeoutExceptionWithPreviousException(): void
    {
        $previousException = new \Exception("Previous error");
        $exception = new RunTimeoutException("Test message", 0, $previousException);

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals("Test message", $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
