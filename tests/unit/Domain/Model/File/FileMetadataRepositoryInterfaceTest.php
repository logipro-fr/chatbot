<?php

namespace Chatbot\Tests\Unit\Domain\Model\File;

use Chatbot\Domain\Model\File\FileId;
use Chatbot\Domain\Model\File\FileMetadata;
use Chatbot\Domain\Model\File\FileMetadataRepositoryInterface;
use PHPUnit\Framework\TestCase;

class FileMetadataRepositoryInterfaceTest extends TestCase
{
    public function testFileMetadataRepositoryInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(FileMetadataRepositoryInterface::class));
    }

    public function testFileMetadataRepositoryInterfaceHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass(FileMetadataRepositoryInterface::class);

        $this->assertTrue($reflection->hasMethod('save'));
        $this->assertTrue($reflection->hasMethod('findById'));
        $this->assertTrue($reflection->hasMethod('findAll'));
        $this->assertTrue($reflection->hasMethod('delete'));
        $this->assertTrue($reflection->hasMethod('findByPurpose'));
    }

    public function testSaveMethodSignature(): void
    {
        $reflection = new \ReflectionClass(FileMetadataRepositoryInterface::class);
        $method = $reflection->getMethod('save');

        $this->assertEquals(1, $method->getNumberOfParameters());
        $this->assertEquals(FileMetadata::class, $method->getParameters()[0]->getType()->getName());
        $this->assertEquals('void', $method->getReturnType()->getName());
    }

    public function testFindByIdMethodSignature(): void
    {
        $reflection = new \ReflectionClass(FileMetadataRepositoryInterface::class);
        $method = $reflection->getMethod('findById');

        $this->assertEquals(1, $method->getNumberOfParameters());
        $this->assertEquals(FileId::class, $method->getParameters()[0]->getType()->getName());
        $returnType = $method->getReturnType();
        $this->assertTrue($returnType->allowsNull());
        $this->assertEquals(FileMetadata::class, $returnType->getName());
    }
}
