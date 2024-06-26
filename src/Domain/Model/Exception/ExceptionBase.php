<?php

namespace Chatbot\Domain\Model\Exceptions;

use Exception;


class BaseException extends Exception
{
    protected string $type = "exception";

    public function __construct(
        string $message = "",
        int $code = 0,
        private mixed $data = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
