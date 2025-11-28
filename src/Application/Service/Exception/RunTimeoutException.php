<?php

namespace Chatbot\Application\Service\Exception;

class RunTimeoutException extends \RuntimeException
{
    public function __construct(
        string $message = "Timeout: Le run n'a pas été complété dans les temps",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
