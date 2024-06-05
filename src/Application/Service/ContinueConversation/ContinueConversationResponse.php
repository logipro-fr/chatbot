<?php

namespace Chatbot\Application\Service\ContinueConversation;

class ContinueConversationResponse
{
    public function __construct(public readonly string $conversationId)
    {
    }
}
