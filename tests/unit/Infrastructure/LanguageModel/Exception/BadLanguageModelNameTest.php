<?php

namespace Chatbot\Tests\Infrastructure\LanguageModel\Exception;

use Chatbot\Infrastructure\LanguageModel\Exception\BadLanguageModelName;
use PHPUnit\Framework\TestCase;

class BadLanguageModelNameTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(BadLanguageModelName::class);

        throw new BadLanguageModelName();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(BadLanguageModelName::class);
        $this->expectExceptionMessage($message);

        throw new BadLanguageModelName($message);
    }
}
