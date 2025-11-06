<?php

namespace Chatbot\Tests\Domain\Event;

use Chatbot\Domain\Event\ThreadCreated;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Thread\ThreadId;
use PHPUnit\Framework\TestCase;

class ThreadCreatedTest extends TestCase
{
    public function testConstructor(): void
    {
        $threadId = new ThreadId('test-thread-id');
        $assistantId = new AssistantId('test-assistant-id');

        $event = new ThreadCreated($threadId, $assistantId);

        $this->assertEquals($threadId, $event->getThreadId());
        $this->assertEquals($assistantId, $event->getAssistantId());
    }
}
