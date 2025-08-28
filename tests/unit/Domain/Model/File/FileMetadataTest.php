<?php

namespace Chatbot\Tests\Unit\Domain\Model\File;

use Chatbot\Domain\Model\File\FileId;
use Chatbot\Domain\Model\File\FileMetadata;
use PHPUnit\Framework\TestCase;

class FileMetadataTest extends TestCase
{
    public function testCreateFileMetadataWithValidData(): void
    {
        $fileId = new FileId('file_test_123');
        $originalFilename = 'test.pdf';
        $purpose = 'assistants';
        $size = 1024;

        $fileMetadata = new FileMetadata($fileId, $originalFilename, $purpose, $size);

        $this->assertEquals($fileId, $fileMetadata->getFileId());
        $this->assertEquals($originalFilename, $fileMetadata->getOriginalFilename());
        $this->assertEquals($purpose, $fileMetadata->getPurpose());
        $this->assertEquals($size, $fileMetadata->getSize());
        $this->assertInstanceOf(\DateTime::class, $fileMetadata->getCreatedAt());
    }

    public function testCreateFileMetadataWithEmptyOriginalFilename(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom de fichier ne peut pas être vide');

        new FileMetadata(new FileId(), '', 'assistants', 1024);
    }

    public function testCreateFileMetadataWithEmptyPurpose(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le purpose ne peut pas être vide');

        new FileMetadata(new FileId(), 'test.pdf', '', 1024);
    }

    public function testCreateFileMetadataWithNegativeSize(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La taille doit être positive');

        new FileMetadata(new FileId(), 'test.pdf', 'assistants', -1);
    }

    public function testCreateFileMetadataWithZeroSize(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La taille doit être positive');

        new FileMetadata(new FileId(), 'test.pdf', 'assistants', 0);
    }

    public function testEquals(): void
    {
        $fileId = new FileId('file_test_equals');
        $fileMetadata1 = new FileMetadata($fileId, 'test1.pdf', 'assistants', 1024);
        $fileMetadata2 = new FileMetadata($fileId, 'test2.pdf', 'fine-tune', 2048);
        $fileMetadata3 = new FileMetadata(new FileId('file_different'), 'test3.pdf', 'assistants', 1024);

        $this->assertTrue($fileMetadata1->equals($fileMetadata2));
        $this->assertFalse($fileMetadata1->equals($fileMetadata3));
    }
}
