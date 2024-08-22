<?php

namespace Chatbot\Tests\Domain\Shared\Exceptions;

use Chatbot\Domain\Shared\Exceptions\LoggedException;
use Chatbot\Domain\Shared\FileManagement\BaseDir;
use PHPUnit\Framework\TestCase;

use function Safe\file_get_contents;

class LoggedExceptionTest extends TestCase
{
    public function testCreate(): void
    {
        $error = new LoggedException("Test exception", 1);
        $this->assertInstanceOf(LoggedException::class, $error);
        $this->assertEquals("Test exception", $error->getMessage());
        $this->assertEquals(1, $error->getCode());
    }

    public function testLog(): void
    {
        $logFilePath = BaseDir::getPathTo(LoggedException::LOG_FILE);
        if (file_exists($logFilePath)) {
            unlink($logFilePath);
        }

        new LoggedException("Test exception is logged", 200);

        $logFile = file_get_contents($logFilePath);
        $this->assertStringContainsString(
            "logger.ERROR: " . LoggedException::class . " Test exception is logged",
            $logFile
        );
    }
}
