<?php

namespace Chatbot\Application\Service\ChangeContextConversation;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\ConversationId;

class ChangeContextConversationRequest{public function __construct(
    public readonly ContextId $contextId,
    public readonly ConversationId $conversation,
    ) {
    }
}