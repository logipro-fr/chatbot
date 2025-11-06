<?php

namespace Chatbot\Application\Service\ContinueAssistantConversation;

use Chatbot\Domain\Model\Conversation\ConversationId;

class ContinueAssistantConversationRequest
{
    public function __construct(
        public readonly ConversationId $conversationId,
        public readonly string $message
    ) {
    }
}
