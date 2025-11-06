<?php

namespace Chatbot\Tests\Domain\Event;

use Chatbot\Domain\Event\ContextEdited;
use PHPUnit\Framework\TestCase;

class ContextEditedTest extends TestCase
{
    public function testConstructor(): void
    {
        $contextId = 'test-context-id';
        $contextMessage = 'Updated context message';

        $event = new ContextEdited($contextId, $contextMessage);

        $this->assertEquals($contextId, $event->contextId);
        $this->assertEquals($contextMessage, $event->contextMessage);
    }
}
