<?php

namespace Chatbot\Application\Service\ViewConversation;



class ViewConversationResponse
{
    public function __construct(
        public readonly string $contextId,
        public readonly array $pairs 
    ) {
    }
}
