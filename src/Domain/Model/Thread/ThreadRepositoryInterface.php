<?php

namespace Chatbot\Domain\Model\Thread;

use Chatbot\Domain\Model\Assistant\AssistantId;

interface ThreadRepositoryInterface
{
    public function add(Thread $thread): void;
    public function findById(ThreadId $threadId): ?Thread;
    public function findByAssistantId(AssistantId $assistantId): array;
    public function findAll(): array;
    public function delete(ThreadId $threadId): void;
}
