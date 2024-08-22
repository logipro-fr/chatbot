<?php

namespace Chatbot\Tests\Domain\Shared\Exceptions;

use Chatbot\Domain\Shared\Exceptions\LoggedException;

class TestException extends LoggedException
{
    protected const MESSAGE = "Test exception";
    protected const ERROR_CODE = 123;

    public function __construct(
        string $message = self::MESSAGE,
        int $code = self::ERROR_CODE
    ) {
        parent::__construct($message, $code);
    }
}
