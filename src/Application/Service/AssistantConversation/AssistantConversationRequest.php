<?php

namespace Chatbot\Application\Service\AssistantConversation;

use Chatbot\Domain\Model\Assistant\Assistant;

class AssistantConversationRequest
{
    public function __construct(
        public Assistant $assistant,
        public string $message
    ) {
    }
}
