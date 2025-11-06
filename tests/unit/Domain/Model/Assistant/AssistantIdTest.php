<?php

namespace Chatbot\Tests\Domain\Model\Assistant;

use Chatbot\Domain\Model\Assistant\AssistantId;
use PHPUnit\Framework\TestCase;

class AssistantIdTest extends TestCase
{
    public function testConstructorWithNullGeneratesUniqueId(): void
    {
        $assistantId = new AssistantId();

        $this->assertStringStartsWith('ast_', $assistantId->getId());
        $this->assertNotEmpty($assistantId->getId());
    }

    public function testConstructorWithProvidedId(): void
    {
        $id = 'custom-assistant-id';
        $assistantId = new AssistantId($id);

        $this->assertEquals($id, $assistantId->getId());
    }

    public function testToString(): void
    {
        $id = 'test-assistant-id';
        $assistantId = new AssistantId($id);

        $this->assertEquals($id, (string) $assistantId);
    }

    public function testEqualsWithSameId(): void
    {
        $id = 'test-assistant-id';
        $assistantId1 = new AssistantId($id);
        $assistantId2 = new AssistantId($id);

        $this->assertTrue($assistantId1->equals($assistantId2));
    }

    public function testEqualsWithDifferentId(): void
    {
        $assistantId1 = new AssistantId('id1');
        $assistantId2 = new AssistantId('id2');

        $this->assertFalse($assistantId1->equals($assistantId2));
    }

    public function testJsonSerialize(): void
    {
        $id = 'test-assistant-id';
        $assistantId = new AssistantId($id);

        $this->assertEquals($id, $assistantId->jsonSerialize());
    }
}
