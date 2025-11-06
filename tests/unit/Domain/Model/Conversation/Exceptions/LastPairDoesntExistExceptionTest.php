<?php

namespace Chatbot\Tests\Domain\Model\Conversation\Exceptions;

use Chatbot\Domain\Model\Conversation\Exceptions\LastPairDoesntExistException;
use PHPUnit\Framework\TestCase;

class LastPairDoesntExistExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(LastPairDoesntExistException::class);

        throw new LastPairDoesntExistException();
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';

        $this->expectException(LastPairDoesntExistException::class);
        $this->expectExceptionMessage($message);

        throw new LastPairDoesntExistException($message);
    }
}
