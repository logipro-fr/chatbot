<?php

namespace Chatbot\Tests\Domain\Shared\FileManagement;

use Chatbot\Domain\Shared\FileManagement\BaseDir;
use PHPUnit\Framework\TestCase;

class BaseDirTest extends TestCase
{
    public function testGetPathTo(): void
    {
        $path = BaseDir::getPathTo("/log");

        $this->assertStringEndsWith("chatbot/log", $path);
    }
}
