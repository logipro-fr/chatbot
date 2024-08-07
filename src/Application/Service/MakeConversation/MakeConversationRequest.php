<?php

namespace Chatbot\Application\Service\MakeConversation;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Prompt;

class MakeConversationRequest
{
    public function __construct(
        public readonly Prompt $prompt,
        public readonly string $lmname,
        public readonly ContextId $context
    ) {
    }
}
