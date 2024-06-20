<?php

namespace Chatbot\Application\Service\ContinueConversation;

use Chatbot\Domain\Model\Conversation\ConversationId;

class ContinueConversationRequest
{
    public function __construct(
        public readonly string $prompt,
        public readonly ConversationId $convId,
        public readonly string $lmName
    ) {
    }
}
