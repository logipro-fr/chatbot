<?php

namespace Chatbot\Domain\Model\File;

use Chatbot\Domain\Model\File\FileId;

class FileMetadata
{
    public function __construct(
        private FileId $fileId,
        private string $originalFilename,
        private string $purpose,
        private int $size,
        private \DateTime $createdAt = new \DateTime()
    ) {
        if (empty($originalFilename)) {
            throw new \InvalidArgumentException('Le nom de fichier ne peut pas être vide');
        }

        if (empty($purpose)) {
            throw new \InvalidArgumentException('Le purpose ne peut pas être vide');
        }

        if ($size <= 0) {
            throw new \InvalidArgumentException('La taille doit être positive');
        }
    }

    public function getFileId(): FileId
    {
        return $this->fileId;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function getPurpose(): string
    {
        return $this->purpose;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function equals(FileMetadata $other): bool
    {
        return $this->fileId->equals($other->fileId);
    }
}
