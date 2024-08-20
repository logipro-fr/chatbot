<?php

namespace Chatbot\Application\Service\ContinueConversation;

use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Pair;

class ContinueConversationResponse
{
    public function __construct(
        public readonly ConversationId $conversationId,
        public readonly Pair $pair,
        public readonly int $numberOfPairs,
        public readonly string $botMessage
    ) {
    }
}
