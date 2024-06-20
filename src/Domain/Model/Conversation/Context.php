<?php

namespace Chatbot\Domain\Model\Conversation;

class Context
{
    public function __construct(private string $context)
    {
    }

    public function getContext(): string
    {
        return $this->context;
    }
}
