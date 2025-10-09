<?php

namespace Chatbot\Tests\Domain\Model\Thread;

use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Thread\Thread;
use Chatbot\Domain\Model\Thread\ThreadId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ThreadTest extends TestCase
{
    public function testConstructor(): void
    {
        $threadId = new ThreadId('test-thread-id');
        $assistantId = new AssistantId('test-assistant-id');
        $externalThreadId = 'external-thread-123';
        $conversationId = new ConversationId('test-conversation-id');
        $createdAt = new DateTimeImmutable('2023-01-01 12:00:00');

        $thread = new Thread(
            $threadId,
            $assistantId,
            $externalThreadId,
            $conversationId,
            $createdAt
        );

        $this->assertEquals($threadId, $thread->getThreadId());
        $this->assertEquals($assistantId, $thread->getAssistantId());
        $this->assertEquals($externalThreadId, $thread->getExternalThreadId());
        $this->assertEquals($conversationId, $thread->getConversationId());
        $this->assertEquals($createdAt, $thread->getCreatedAt());
    }

    public function testConstructorWithDefaultCreatedAt(): void
    {
        $threadId = new ThreadId('test-thread-id');
        $assistantId = new AssistantId('test-assistant-id');
        $externalThreadId = 'external-thread-123';
        $conversationId = new ConversationId('test-conversation-id');

        $thread = new Thread(
            $threadId,
            $assistantId,
            $externalThreadId,
            $conversationId
        );

        $this->assertInstanceOf(DateTimeImmutable::class, $thread->getCreatedAt());
        $this->assertLessThanOrEqual(new DateTimeImmutable(), $thread->getCreatedAt());
    }

    public function testGetThreadId(): void
    {
        $threadId = new ThreadId('test-thread-id');
        $assistantId = new AssistantId('test-assistant-id');
        $externalThreadId = 'external-thread-123';
        $conversationId = new ConversationId('test-conversation-id');

        $thread = new Thread($threadId, $assistantId, $externalThreadId, $conversationId);

        $this->assertEquals($threadId, $thread->getThreadId());
    }

    public function testGetAssistantId(): void
    {
        $threadId = new ThreadId('test-thread-id');
        $assistantId = new AssistantId('test-assistant-id');
        $externalThreadId = 'external-thread-123';
        $conversationId = new ConversationId('test-conversation-id');

        $thread = new Thread($threadId, $assistantId, $externalThreadId, $conversationId);

        $this->assertEquals($assistantId, $thread->getAssistantId());
    }

    public function testGetExternalThreadId(): void
    {
        $threadId = new ThreadId('test-thread-id');
        $assistantId = new AssistantId('test-assistant-id');
        $externalThreadId = 'external-thread-123';
        $conversationId = new ConversationId('test-conversation-id');

        $thread = new Thread($threadId, $assistantId, $externalThreadId, $conversationId);

        $this->assertEquals($externalThreadId, $thread->getExternalThreadId());
    }

    public function testGetConversationId(): void
    {
        $threadId = new ThreadId('test-thread-id');
        $assistantId = new AssistantId('test-assistant-id');
        $externalThreadId = 'external-thread-123';
        $conversationId = new ConversationId('test-conversation-id');

        $thread = new Thread($threadId, $assistantId, $externalThreadId, $conversationId);

        $this->assertEquals($conversationId, $thread->getConversationId());
    }

    public function testGetCreatedAt(): void
    {
        $threadId = new ThreadId('test-thread-id');
        $assistantId = new AssistantId('test-assistant-id');
        $externalThreadId = 'external-thread-123';
        $conversationId = new ConversationId('test-conversation-id');
        $createdAt = new DateTimeImmutable('2023-01-01 12:00:00');

        $thread = new Thread($threadId, $assistantId, $externalThreadId, $conversationId, $createdAt);

        $this->assertEquals($createdAt, $thread->getCreatedAt());
    }
}
