<?php

namespace Chatbot\Application\Service;

use Chatbot\Domain\Model\Conversation\Prompt;

class Request implements RequestInterface
{
    public function __construct(public readonly Prompt $prompt)
    {
    }
}
