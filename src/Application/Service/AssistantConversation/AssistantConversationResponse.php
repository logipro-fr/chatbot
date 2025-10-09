<?php

namespace Chatbot\Application\Service\AssistantConversation;

class AssistantConversationResponse
{
    public function __construct(
        public string $conversationId,
        public string $assistantMessage
    ) {
    }
}
