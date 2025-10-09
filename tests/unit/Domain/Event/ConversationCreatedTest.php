<?php

namespace Chatbot\Tests\Domain\Event;

use Chatbot\Domain\Event\ConversationCreated;
use PHPUnit\Framework\TestCase;

class ConversationCreatedTest extends TestCase
{
    public function testConstructor(): void
    {
        $conversationId = 'test-conversation-id';

        $event = new ConversationCreated($conversationId);

        $this->assertEquals($conversationId, $event->conversationId);
    }
}
