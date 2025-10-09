<?php

namespace Chatbot\Tests\Domain\Model\Conversation\Exceptions;

use Chatbot\Domain\Model\Conversation\Exceptions\PairOutOfRangeException;
use PHPUnit\Framework\TestCase;

class PairOutOfRangeExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(PairOutOfRangeException::class);

        throw new PairOutOfRangeException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(PairOutOfRangeException::class);
        $this->expectExceptionMessage($message);

        throw new PairOutOfRangeException($message);
    }
}
