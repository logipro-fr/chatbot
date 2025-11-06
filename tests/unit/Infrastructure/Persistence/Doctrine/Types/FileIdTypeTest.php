<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\File\FileId;
use Chatbot\Infrastructure\Persistence\Doctrine\Types\FileIdType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class FileIdTypeTest extends TestCase
{
    private FileIdType $type;
    private AbstractPlatform&MockObject $platform;

    protected function setUp(): void
    {
        $this->type = new FileIdType();
        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    public function testGetSQLDeclaration(): void
    {
        $column = ['length' => 255];
        $this->platform->expects($this->once())
            ->method('getStringTypeDeclarationSQL')
            ->with($column)
            ->willReturn('VARCHAR(255)');

        $result = $this->type->getSQLDeclaration($column, $this->platform);
        $this->assertEquals('VARCHAR(255)', $result);
    }

    public function testConvertToPHPValueWithValidString(): void
    {
        $value = 'test-file-id';
        $result = $this->type->convertToPHPValue($value, $this->platform);

        $this->assertInstanceOf(FileId::class, $result);
        $this->assertEquals($value, $result->getId());
    }

    public function testConvertToPHPValueWithNull(): void
    {
        $result = $this->type->convertToPHPValue(null, $this->platform);
        $this->assertNull($result);
    }

    public function testConvertToPHPValueWithInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected string value for FileId');

        $this->type->convertToPHPValue(123, $this->platform);
    }

    public function testConvertToDatabaseValueWithFileId(): void
    {
        $fileId = new FileId('test-file-id');
        $result = $this->type->convertToDatabaseValue($fileId, $this->platform);

        $this->assertEquals('test-file-id', $result);
    }

    public function testConvertToDatabaseValueWithString(): void
    {
        $value = 'test-file-id';
        $result = $this->type->convertToDatabaseValue($value, $this->platform);

        $this->assertEquals($value, $result);
    }

    public function testConvertToDatabaseValueWithNull(): void
    {
        $result = $this->type->convertToDatabaseValue(null, $this->platform);
        $this->assertNull($result);
    }

    public function testConvertToDatabaseValueWithInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected FileId or string value');

        $this->type->convertToDatabaseValue(123, $this->platform);
    }

    public function testGetName(): void
    {
        $this->assertEquals('fil_id', $this->type->getName());
    }
}
