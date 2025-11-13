<?php

namespace Chatbot\Application\Service\ListConversations;

class ListConversationsResponse
{
    /**
     * @param array<int, ListConversationItem> $conversations
     */
    public function __construct(
        public readonly array $conversations
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $conversationsArray = [];
        foreach ($this->conversations as $conversation) {
            $conversationsArray[] = $conversation->toArray();
        }

        return [
            'conversations' => $conversationsArray,
        ];
    }
}

