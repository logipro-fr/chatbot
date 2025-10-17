<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Thread;

use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Thread\Thread;
use Chatbot\Domain\Model\Thread\ThreadId;
use Chatbot\Infrastructure\Exception\ThreadNotFoundException;
use Chatbot\Infrastructure\Persistence\Thread\ThreadRepositoryInMemory;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ThreadRepositoryInMemoryTest extends TestCase
{
    private ThreadRepositoryInMemory $repository;

    protected function setUp(): void
    {
        $this->repository = new ThreadRepositoryInMemory();
    }

    public function testAddAndFindById(): void
    {
        $thread = $this->createThread();
        $this->repository->add($thread);

        $found = $this->repository->findById($thread->getThreadId());

        $this->assertNotNull($found);
        $this->assertEquals($thread->getThreadId(), $found->getThreadId());
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $threadId = new ThreadId('non-existent-id');
        $found = $this->repository->findById($threadId);

        $this->assertNull($found);
    }

    public function testFindByAssistantId(): void
    {
        $assistantId1 = new AssistantId('assistant-1');
        $assistantId2 = new AssistantId('assistant-2');

        $thread1 = $this->createThread('thread-1', $assistantId1);
        $thread2 = $this->createThread('thread-2', $assistantId1);
        $thread3 = $this->createThread('thread-3', $assistantId2);

        $this->repository->add($thread1);
        $this->repository->add($thread2);
        $this->repository->add($thread3);

        $threadsForAssistant1 = $this->repository->findByAssistantId($assistantId1);
        $threadsForAssistant2 = $this->repository->findByAssistantId($assistantId2);

        $this->assertCount(2, $threadsForAssistant1);
        $this->assertCount(1, $threadsForAssistant2);
    }

    public function testFindByConversationId(): void
    {
        $conversationId = new ConversationId('conversation-1');
        $thread = $this->createThread('thread-1', null, $conversationId);

        $this->repository->add($thread);

        $found = $this->repository->findByConversationId($conversationId);

        $this->assertNotNull($found);
        $this->assertEquals($conversationId, $found->getConversationId());
    }

    public function testFindByConversationIdReturnsNullWhenNotFound(): void
    {
        $conversationId = new ConversationId('non-existent-conversation');
        $found = $this->repository->findByConversationId($conversationId);

        $this->assertNull($found);
    }

    public function testFindAll(): void
    {
        $thread1 = $this->createThread('thread-1');
        $thread2 = $this->createThread('thread-2');

        $this->repository->add($thread1);
        $this->repository->add($thread2);

        $allThreads = $this->repository->findAll();

        $this->assertCount(2, $allThreads);
    }

    public function testDelete(): void
    {
        $thread = $this->createThread();
        $this->repository->add($thread);

        $this->repository->delete($thread->getThreadId());

        $found = $this->repository->findById($thread->getThreadId());
        $this->assertNull($found);
    }

    public function testDeleteThrowsExceptionWhenThreadNotFound(): void
    {
        $threadId = new ThreadId('non-existent-thread');

        $this->expectException(ThreadNotFoundException::class);
        $this->expectExceptionMessage("Le thread avec l'id = $threadId n'a pas été trouvé");

        $this->repository->delete($threadId);
    }

    private function createThread(
        string $threadId = 'test-thread-id',
        ?AssistantId $assistantId = null,
        ?ConversationId $conversationId = null
    ): Thread {
        return new Thread(
            new ThreadId($threadId),
            $assistantId ?? new AssistantId('test-assistant-id'),
            'external-thread-123',
            $conversationId ?? new ConversationId('test-conversation-id')
        );
    }
}
