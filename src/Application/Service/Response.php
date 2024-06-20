<?php

namespace Chatbot\Application\Service;

class Response implements ResponseInterface
{
    public function __construct(public readonly string $message, public readonly int $statusCode)
    {
    }
}
