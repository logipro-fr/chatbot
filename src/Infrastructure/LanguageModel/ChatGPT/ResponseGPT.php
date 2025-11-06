<?php

namespace Chatbot\Infrastructure\LanguageModel\ChatGPT;

use Chatbot\Application\Service\ResponseInterface;

class ResponseGPT implements ResponseInterface
{
    public function __construct(public readonly string $message, public readonly int $statusCode)
    {
    }
}
