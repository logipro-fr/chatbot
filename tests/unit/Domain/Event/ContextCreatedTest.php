<?php

namespace Chatbot\Tests\Domain\Event;

use Chatbot\Domain\Event\ContextCreated;
use PHPUnit\Framework\TestCase;

class ContextCreatedTest extends TestCase
{
    public function testConstructor(): void
    {
        $contextId = 'test-context-id';
        $contextMessage = 'Test context message';

        $event = new ContextCreated($contextId, $contextMessage);

        $this->assertEquals($contextId, $event->contextId);
        $this->assertEquals($contextMessage, $event->contextMessage);
    }
}
