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

    public function testSaveMethodSignature(): void
    {
        $reflection = new \ReflectionClass(FileMetadataRepositoryInterface::class);
        $method = $reflection->getMethod('save');

        $this->assertEquals(1, $method->getNumberOfParameters());
        $parameterType = $method->getParameters()[0]->getType();
        if ($parameterType instanceof \ReflectionNamedType) {
            $this->assertEquals(FileMetadata::class, $parameterType->getName());
        }
        $returnType = $method->getReturnType();
        if ($returnType instanceof \ReflectionNamedType) {
            $this->assertEquals('void', $returnType->getName());
        }
    }

    public function testFindByIdMethodSignature(): void
    {
        $reflection = new \ReflectionClass(FileMetadataRepositoryInterface::class);
        $method = $reflection->getMethod('findById');

        $this->assertEquals(1, $method->getNumberOfParameters());
        $parameterType = $method->getParameters()[0]->getType();
        if ($parameterType instanceof \ReflectionNamedType) {
            $this->assertEquals(FileId::class, $parameterType->getName());
        }
        $returnType = $method->getReturnType();
        if ($returnType instanceof \ReflectionNamedType) {
            $this->assertTrue($returnType->allowsNull());
            $this->assertEquals(FileMetadata::class, $returnType->getName());
        }
    }
}
