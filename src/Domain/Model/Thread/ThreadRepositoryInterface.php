<?php

namespace Chatbot\Domain\Model\Thread;

use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Conversation\ConversationId;

interface ThreadRepositoryInterface
{
    public function add(Thread $thread): void;
    public function findById(ThreadId $threadId): ?Thread;
    /**
     * @return array<Thread>
     */
    public function findByAssistantId(AssistantId $assistantId): array;
    public function findByConversationId(ConversationId $conversationId): ?Thread;
    /**
     * @return array<Thread>
     */
    public function findAll(): array;
    public function delete(ThreadId $threadId): void;
}
