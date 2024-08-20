<?php

namespace Chatbot\Application\Service\SwitchContextConversation;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Conversation\ConversationId;

class SwitchContextConversationResponse
{
    public function __construct(
        public readonly string $contextId,
        public readonly string $conversation,
    ) {
    }
}
