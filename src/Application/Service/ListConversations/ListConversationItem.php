<?php

namespace Chatbot\Application\Service\ListConversations;

class ListConversationItem
{
    public function __construct(
        public readonly string $conversationId,
        public readonly string $title,
        public readonly string $contextId,
        public readonly int $numberOfPairs,
        public readonly string $createdAt
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'conversationId' => $this->conversationId,
            'title' => $this->title,
            'contextId' => $this->contextId,
            'numberOfPairs' => $this->numberOfPairs,
            'createdAt' => $this->createdAt,
        ];
    }
}
