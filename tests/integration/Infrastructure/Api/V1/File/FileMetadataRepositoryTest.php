<?php

namespace Chatbot\Tests\Integration\Infrastructure\Api\V1\File;

use Chatbot\Domain\Model\File\FileId;
use Chatbot\Domain\Model\File\FileMetadata;
use Chatbot\Domain\Model\File\FileMetadataRepositoryInterface;

class FileMetadataRepositoryTest implements FileMetadataRepositoryInterface
{
    public function save(FileMetadata $fileMetadata): void
    {
    }

    public function findById(FileId $fileId): ?FileMetadata
    {
        return null;
    }

    public function findAll(): array
    {
        return [];
    }

    public function delete(FileId $fileId): void
    {
    }

    public function findByPurpose(string $purpose): array
    {
        return [];
    }
}
