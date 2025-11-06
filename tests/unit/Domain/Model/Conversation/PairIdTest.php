<?php

namespace Chatbot\Tests\Domain\Model\Conversation;

use Chatbot\Domain\Model\Conversation\PairId;
use PHPUnit\Framework\TestCase;

class PairIdTest extends TestCase
{
    public function testConstructorWithEmptyIdGeneratesUniqueId(): void
    {
        $pairId = new PairId();

        $this->assertStringStartsWith('pai_', $pairId->getId());
        $this->assertNotEmpty($pairId->getId());
    }

    public function testConstructorWithProvidedId(): void
    {
        $id = 'custom-pair-id';
        $pairId = new PairId($id);

        $this->assertEquals($id, $pairId->getId());
    }

    public function testToString(): void
    {
        $id = 'test-pair-id';
        $pairId = new PairId($id);

        $this->assertEquals($id, (string) $pairId);
    }

    public function testEqualsWithSameId(): void
    {
        $id = 'test-pair-id';
        $pairId1 = new PairId($id);
        $pairId2 = new PairId($id);

        $this->assertTrue($pairId1->equals($pairId2));
    }

    public function testEqualsWithDifferentId(): void
    {
        $pairId1 = new PairId('id1');
        $pairId2 = new PairId('id2');

        $this->assertFalse($pairId1->equals($pairId2));
    }
}
