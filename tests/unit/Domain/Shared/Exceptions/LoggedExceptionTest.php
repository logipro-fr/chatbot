<?php

namespace Chatbot\Tests\Domain\Shared\Exceptions;

use Chatbot\Domain\Shared\Exceptions\LoggedException;
use Chatbot\Domain\Shared\FileManagement\BaseDir;
use PHPUnit\Framework\TestCase;

use function Safe\file_get_contents;

class LoggedExceptionTest extends TestCase
{
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
