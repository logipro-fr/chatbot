<?php

namespace Chatbot\Application\Service\ViewConversation;

use Chatbot\Domain\Model\Conversation\Pair;

class ViewConversationResponse
{
    /**
     * @param array<int,Pair>$pairs
     */
    public function __construct(
        public readonly string $contextId,
        public readonly array $pairs
    ) {
    }
}
