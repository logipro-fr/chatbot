<?php

namespace Chatbot\Infrastructure\Persistence\File;

use Chatbot\Domain\Model\File\FileId;
use Chatbot\Domain\Model\File\FileMetadata;
use Chatbot\Domain\Model\File\FileMetadataRepositoryInterface;

class FileMetadataRepositoryInMemory implements FileMetadataRepositoryInterface
{
    /** @var array<string, FileMetadata> */
    private array $files = [];

    public function save(FileMetadata $fileMetadata): void
    {
        $this->files[$fileMetadata->getFileId()->getId()] = $fileMetadata;
    }

    public function findById(FileId $fileId): ?FileMetadata
    {
        return $this->files[$fileId->getId()] ?? null;
    }

    /**
     * @return array<FileMetadata>
     */
    public function findAll(): array
    {
        return array_values($this->files);
    }

    public function delete(FileId $fileId): void
    {
        unset($this->files[$fileId->getId()]);
    }

    /**
     * @return array<FileMetadata>
     */
    public function findByPurpose(string $purpose): array
    {
        return array_values(array_filter($this->files, function (FileMetadata $file) use ($purpose) {
            return $file->getPurpose() === $purpose;
        }));
    }
}
