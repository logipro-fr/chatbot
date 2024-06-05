<?php

namespace Chatbot\Domain\Model\Conversation;

class Context
{
    public function __construct(public readonly string $context)
    {
    }
}
