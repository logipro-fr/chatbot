<?php

namespace Chatbot\Application\Service\ContinueAssistantConversation;

class ContinueAssistantConversationResponse
{
    public function __construct(
        public readonly string $conversationId,
        public readonly string $assistantMessage
    ) {
    }
}
