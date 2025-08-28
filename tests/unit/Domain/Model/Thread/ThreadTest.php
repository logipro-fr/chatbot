<?php

namespace Chatbot\Tests\Unit\Domain\Model\Thread;

use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Thread\Thread;
use Chatbot\Domain\Model\Thread\ThreadId;
use PHPUnit\Framework\TestCase;

class ThreadTest extends TestCase
{
    public function test_should_create_thread_with_assistant(): void
    {
        // Given
        $threadId = new ThreadId();
        $assistantId = new AssistantId();
        $openAiThreadId = "thread_123456";

        // When
        $thread = new Thread($threadId, $assistantId, $openAiThreadId);

        // Then
        $this->assertEquals($threadId, $thread->getThreadId());
        $this->assertEquals($assistantId, $thread->getAssistantId());
        $this->assertEquals($openAiThreadId, $thread->getOpenAiThreadId());
    }

    public function test_should_have_created_at_timestamp(): void
    {
        // Given
        $thread = new Thread(
            new ThreadId(),
            new AssistantId(),
            "thread_123"
        );

        // When & Then
        $this->assertInstanceOf(\DateTimeImmutable::class, $thread->getCreatedAt());
    }
}
