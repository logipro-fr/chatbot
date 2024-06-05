<?php

namespace Chatbot\Tests\Domain;

use Chatbot\Domain\Exception\MissingId;
use Chatbot\Domain\Model\Conversation\ConversationId;
use PHPUnit\Framework\TestCase;

class ConversationIdTest extends TestCase
{
    public function testIndentify(): void
    {
        $id1 = new ConversationId();
        $id2 = new ConversationId();
        $this->assertFalse($id1->equals($id2));
    }



    public function testIndentify2(): void
    {
        $id1 = new ConversationId();
        $this->assertTrue($id1->equals($id1));
    }

    public function testValueId(): void
    {
        $id = new ConversationId("con_id");
        $this->assertEquals("con_id", $id);
    }
}
