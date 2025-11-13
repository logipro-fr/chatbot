<?php

namespace Chatbot\Application\Service\ListConversations;

use Chatbot\Domain\Model\Assistant\AssistantId;

class ListConversationsRequest
{
    public function __construct(
        public readonly AssistantId $assistantId
    ) {
    }
}

