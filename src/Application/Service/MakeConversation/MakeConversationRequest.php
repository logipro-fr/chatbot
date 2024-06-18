<?php

namespace Chatbot\Application\Service\MakeConversation;

class MakeConversationRequest
{
    public function __construct(
        public readonly string $prompt,
        public readonly string $lmname,
        public readonly string $context
    ) {
    }
}
