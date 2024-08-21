<?php

namespace Chatbot\Application\Service\MakeConversation;

use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Pair;

class MakeConversationResponse
{
    public function __construct(
        public readonly string $conversationId,
        public readonly int $numberOfPairs,
        public readonly string $botMessage
    ) {
    }
}
