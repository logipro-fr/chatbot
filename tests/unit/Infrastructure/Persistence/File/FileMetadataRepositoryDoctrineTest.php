<?php

namespace Chatbot\Tests\Infrastructure\Persistence\File;

use Chatbot\Infrastructure\Persistence\File\FileMetadataRepositoryDoctrine;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;

class FileMetadataRepositoryDoctrineTest extends FileMetadataRepositoryTestBase
{
    use DoctrineRepositoryTesterTrait;

    protected function initialize(): void
    {
        $this->initDoctrineTester();
        $this->clearTables(["files"]);
        $this->repository = new FileMetadataRepositoryDoctrine($this->getEntityManager());
    }
}
