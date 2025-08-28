<?php

namespace Chatbot\Application\Service\AssistantConversation;

use Chatbot\Domain\Model\Conversation\ConversationId;

class AssistantConversationResponse
{
    public function __construct(
        public ConversationId $conversationId,
        public string $assistantMessage
    ) {
    }
}
