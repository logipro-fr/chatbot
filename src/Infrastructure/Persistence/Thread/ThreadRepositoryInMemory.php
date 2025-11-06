<?php

namespace Chatbot\Infrastructure\Persistence\Thread;

use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Thread\Thread;
use Chatbot\Domain\Model\Thread\ThreadId;
use Chatbot\Domain\Model\Thread\ThreadRepositoryInterface;
use Chatbot\Infrastructure\Exception\ThreadNotFoundException;

class ThreadRepositoryInMemory implements ThreadRepositoryInterface
{
    /** @var array<string, Thread> */
    private array $threads = [];

    public function add(Thread $thread): void
    {
        $this->threads[$thread->getThreadId()->getId()] = $thread;
    }

    public function findById(ThreadId $threadId): ?Thread
    {
        return $this->threads[$threadId->getId()] ?? null;
    }

    /**
     * @return array<Thread>
     */
    public function findByAssistantId(AssistantId $assistantId): array
    {
        return array_values(array_filter($this->threads, function (Thread $thread) use ($assistantId) {
            return $thread->getAssistantId()->equals($assistantId);
        }));
    }

    public function findByConversationId(ConversationId $conversationId): ?Thread
    {
        foreach ($this->threads as $thread) {
            if ($thread->getConversationId()->equals($conversationId)) {
                return $thread;
            }
        }
        return null;
    }

    /**
     * @return array<Thread>
     */
    public function findAll(): array
    {
        return array_values($this->threads);
    }

    public function delete(ThreadId $threadId): void
    {
        if (!isset($this->threads[$threadId->getId()])) {
            throw new ThreadNotFoundException("Le thread avec l'id = $threadId n'a pas été trouvé");
        }
        unset($this->threads[$threadId->getId()]);
    }
}
