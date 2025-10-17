<?php

namespace Chatbot\Tests\Infrastructure\Persistence\File;

use Chatbot\Infrastructure\Persistence\File\FileMetadataRepositoryInMemory;

class FileMetadataRepositoryInMemoryTest extends FileMetadataRepositoryTestBase
{
    protected function initialize(): void
    {
        $this->repository = new FileMetadataRepositoryInMemory();
    }
}
