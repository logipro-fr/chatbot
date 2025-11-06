<?php

namespace Chatbot\Tests\Infrastructure\Persistence\File;

use Chatbot\Domain\Model\File\FileId;
use Chatbot\Domain\Model\File\FileMetadata;
use Chatbot\Domain\Model\File\FileMetadataRepositoryInterface;
use PHPUnit\Framework\TestCase;

abstract class FileMetadataRepositoryTestBase extends TestCase
{
    protected FileMetadataRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->initialize();
    }

    abstract protected function initialize(): void;

    public function testSave(): void
    {
        $fileId = new FileId("test-file-id");
        $fileMetadata = new FileMetadata(
            $fileId,
            "test-file.txt",
            "test-purpose",
            1024
        );

        $this->repository->save($fileMetadata);

        $found = $this->repository->findById($fileId);
        $this->assertNotNull($found);
        $this->assertEquals($fileId, $found->getFileId());
        $this->assertEquals("test-file.txt", $found->getOriginalFilename());
        $this->assertEquals("test-purpose", $found->getPurpose());
        $this->assertEquals(1024, $found->getSize());
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $fileId = new FileId("non-existent-id");

        $found = $this->repository->findById($fileId);

        $this->assertNull($found);
    }

    public function testFindAllReturnsEmptyArrayWhenNoFiles(): void
    {
        $files = $this->repository->findAll();

        $this->assertEmpty($files);
    }

    public function testFindAllReturnsAllFiles(): void
    {
        $fileId1 = new FileId("file-1");
        $fileId2 = new FileId("file-2");

        $fileMetadata1 = new FileMetadata($fileId1, "file1.txt", "purpose1", 100);
        $fileMetadata2 = new FileMetadata($fileId2, "file2.txt", "purpose2", 200);

        $this->repository->save($fileMetadata1);
        $this->repository->save($fileMetadata2);

        $files = $this->repository->findAll();

        $this->assertCount(2, $files);
        $this->assertContainsOnlyInstancesOf(FileMetadata::class, $files);
    }

    public function testDeleteRemovesFileWhenExists(): void
    {
        $fileId = new FileId("file-to-delete");
        $fileMetadata = new FileMetadata($fileId, "delete-me.txt", "temp", 50);

        $this->repository->save($fileMetadata);
        $this->repository->delete($fileId);

        $found = $this->repository->findById($fileId);
        $this->assertNull($found);
    }

    public function testDeleteDoesNothingWhenFileDoesNotExist(): void
    {
        $fileId = new FileId("non-existent-file");

        $this->repository->delete($fileId);

        $this->assertNull($this->repository->findById($fileId));
    }

    public function testFindByPurposeReturnsEmptyArrayWhenNoMatches(): void
    {
        $files = $this->repository->findByPurpose("non-existent-purpose");

        $this->assertEmpty($files);
    }

    public function testFindByPurposeReturnsMatchingFiles(): void
    {
        $fileId1 = new FileId("file-1");
        $fileId2 = new FileId("file-2");
        $fileId3 = new FileId("file-3");

        $fileMetadata1 = new FileMetadata($fileId1, "file1.txt", "upload", 100);
        $fileMetadata2 = new FileMetadata($fileId2, "file2.txt", "download", 200);
        $fileMetadata3 = new FileMetadata($fileId3, "file3.txt", "upload", 300);

        $this->repository->save($fileMetadata1);
        $this->repository->save($fileMetadata2);
        $this->repository->save($fileMetadata3);

        $uploadFiles = $this->repository->findByPurpose("upload");
        $downloadFiles = $this->repository->findByPurpose("download");

        $this->assertCount(2, $uploadFiles);
        $this->assertCount(1, $downloadFiles);
        $this->assertContainsOnlyInstancesOf(FileMetadata::class, $uploadFiles);
        $this->assertContainsOnlyInstancesOf(FileMetadata::class, $downloadFiles);
    }
}
