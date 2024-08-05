<?php

namespace Chatbot\Domain\Model\Context;

use Chatbot\Domain\Model\Conversation\TokenCount;

class ContextMessage{

    public function __construct(public readonly string $context)
    {
    }

    public function countToken(): int
    {
        $tokenCount = new TokenCount();
        $result = $tokenCount->countToken($this->context);
        return $result;
    }
    
    public function getMessage(): string
    {
        return $this->context;
    }

}



