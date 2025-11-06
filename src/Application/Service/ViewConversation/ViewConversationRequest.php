<?php

namespace Chatbot\Application\Service\ViewConversation;

class ViewConversationRequest
{
    public function __construct(
        public readonly string $id
    ) {
    }
}
