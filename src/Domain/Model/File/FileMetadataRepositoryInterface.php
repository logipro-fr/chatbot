<?php

namespace Chatbot\Domain\Model\File;

interface FileMetadataRepositoryInterface
{
    public function save(FileMetadata $fileMetadata): void;

    public function findById(FileId $fileId): ?FileMetadata;

    /**
     * @return array<FileMetadata>
     */
    public function findAll(): array;

    public function delete(FileId $fileId): void;

    /**
     * @return array<FileMetadata>
     */
    public function findByPurpose(string $purpose): array;
}
