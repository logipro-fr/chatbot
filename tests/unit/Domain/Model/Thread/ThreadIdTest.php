<?php

namespace Chatbot\Tests\Domain\Model\Thread;

use Chatbot\Domain\Model\Thread\ThreadId;
use PHPUnit\Framework\TestCase;

class ThreadIdTest extends TestCase
{
    public function testConstructorWithNullGeneratesUniqueId(): void
    {
        $threadId = new ThreadId();

        $this->assertStringStartsWith('thr_', $threadId->getId());
        $this->assertNotEmpty($threadId->getId());
    }

    public function testConstructorWithProvidedId(): void
    {
        $id = 'custom-thread-id';
        $threadId = new ThreadId($id);

        $this->assertEquals($id, $threadId->getId());
    }

    public function testToString(): void
    {
        $id = 'test-thread-id';
        $threadId = new ThreadId($id);

        $this->assertEquals($id, (string) $threadId);
    }

    public function testEqualsWithSameId(): void
    {
        $id = 'test-thread-id';
        $threadId1 = new ThreadId($id);
        $threadId2 = new ThreadId($id);

        $this->assertTrue($threadId1->equals($threadId2));
    }

    public function testEqualsWithDifferentId(): void
    {
        $threadId1 = new ThreadId('id1');
        $threadId2 = new ThreadId('id2');

        $this->assertFalse($threadId1->equals($threadId2));
    }
}
