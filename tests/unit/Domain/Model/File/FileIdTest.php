<?php

namespace Chatbot\Tests\Unit\Domain\Model\File;

use Chatbot\Domain\Model\File\FileId;
use PHPUnit\Framework\TestCase;

class FileIdTest extends TestCase
{
    public function testCreateFileIdWithEmptyId(): void
    {
        $fileId = new FileId();

        $this->assertStringStartsWith('fil_', $fileId->getId());
        $this->assertNotEmpty($fileId->getId());
    }

    public function testCreateFileIdWithCustomId(): void
    {
        $customId = 'fil_custom_123';
        $fileId = new FileId($customId);

        $this->assertEquals($customId, $fileId->getId());
    }

    public function testGetId(): void
    {
        $id = 'fil_test_456';
        $fileId = new FileId($id);

        $this->assertEquals($id, $fileId->getId());
    }

    public function testToString(): void
    {
        $id = 'fil_test_789';
        $fileId = new FileId($id);

        $this->assertEquals($id, (string) $fileId);
    }

    public function testEquals(): void
    {
        $id = 'fil_test_equals';
        $fileId1 = new FileId($id);
        $fileId2 = new FileId($id);
        $fileId3 = new FileId('fil_different');

        $this->assertTrue($fileId1->equals($fileId2));
        $this->assertFalse($fileId1->equals($fileId3));
    }
}
