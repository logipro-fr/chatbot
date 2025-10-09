<?php

namespace Chatbot\Tests\Domain\Event;

use Chatbot\Domain\Event\PairAdded;
use PHPUnit\Framework\TestCase;

class PairAddedTest extends TestCase
{
    public function testConstructor(): void
    {
        $conversationId = 'test-conversation-id';

        $event = new PairAdded($conversationId);

        $this->assertEquals($conversationId, $event->conversationId);
    }
}
