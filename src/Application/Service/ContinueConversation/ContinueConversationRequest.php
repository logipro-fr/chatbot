<?php

namespace Chatbot\Application\Service\ContinueConversation;

use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Prompt;

class ContinueConversationRequest
{
    public function __construct(
        public readonly Prompt $prompt,
        public readonly ConversationId $convId,
        public readonly string $lmName
    ) {
    }
}
