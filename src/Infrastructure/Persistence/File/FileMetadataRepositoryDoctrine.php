<?php

namespace Chatbot\Infrastructure\Persistence\File;

use Chatbot\Domain\Model\File\FileId;
use Chatbot\Domain\Model\File\FileMetadata;
use Chatbot\Domain\Model\File\FileMetadataRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class FileMetadataRepositoryDoctrine implements FileMetadataRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function save(FileMetadata $fileMetadata): void
    {
        $this->entityManager->persist($fileMetadata);
        $this->entityManager->flush();
    }

    public function findById(FileId $fileId): ?FileMetadata
    {
        return $this->entityManager->getRepository(FileMetadata::class)->find($fileId->getId());
    }

    public function findAll(): array
    {
        return $this->entityManager->getRepository(FileMetadata::class)->findAll();
    }

    public function delete(FileId $fileId): void
    {
        $fileMetadata = $this->findById($fileId);
        if ($fileMetadata) {
            $this->entityManager->remove($fileMetadata);
            $this->entityManager->flush();
        }
    }

    public function findByPurpose(string $purpose): array
    {
        return $this->entityManager->getRepository(FileMetadata::class)->findBy(['purpose' => $purpose]);
    }
}
