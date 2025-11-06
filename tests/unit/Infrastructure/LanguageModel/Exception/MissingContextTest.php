<?php

namespace Chatbot\Tests\Infrastructure\LanguageModel\Exception;

use Chatbot\Infrastructure\LanguageModel\Exception\MissingContext;
use PHPUnit\Framework\TestCase;

class MissingContextTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(MissingContext::class);

        throw new MissingContext();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(MissingContext::class);
        $this->expectExceptionMessage($message);

        throw new MissingContext($message);
    }
}
