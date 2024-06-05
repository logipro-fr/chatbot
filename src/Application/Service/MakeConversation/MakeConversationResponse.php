<?php

namespace Chatbot\Application\Service\MakeConversation;

use Chatbot\Domain\Model\Conversation\ConversationId;

class MakeConversationResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly int $statusCode,
        public readonly ConversationId $conversationId,
        public readonly string $message = "",
    ) {
    }
}
