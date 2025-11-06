<?php

namespace Chatbot\Tests\Domain\Event;

use Chatbot\Domain\Event\AssistantCreated;
use Chatbot\Domain\Model\Assistant\AssistantId;
use PHPUnit\Framework\TestCase;

class AssistantCreatedTest extends TestCase
{
    public function testConstructor(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $name = 'Test Assistant';

        $event = new AssistantCreated($assistantId, $name);

        $this->assertEquals($assistantId, $event->getAssistantId());
        $this->assertEquals($name, $event->getName());
    }
}
