<?php

namespace Chatbot\Application\Service\MakeConversation;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MakeConversationRequest
{
    public function __construct(
        public readonly string $prompt,
        public readonly string $lmname,
        public readonly string $context
    ) {
    }
}
