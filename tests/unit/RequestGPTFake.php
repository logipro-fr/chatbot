<?php

namespace Chatbot\Tests;

use Chatbot\Application\Service\RequestInterface;

class RequestGPTFake implements RequestInterface
{
    /**
     * @property-read string $prompt */
    public function __construct(public readonly string $prompt)
    {
    }
}
