<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Thread\ThreadId;
use Chatbot\Infrastructure\Persistence\Doctrine\Types\ThreadIdType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ThreadIdTypeTest extends TestCase
{
    private ThreadIdType $type;
    private AbstractPlatform&MockObject $platform;

    protected function setUp(): void
    {
        $this->type = new ThreadIdType();
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
        $value = 'test-thread-id';
        $result = $this->type->convertToPHPValue($value, $this->platform);

        $this->assertInstanceOf(ThreadId::class, $result);
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
        $this->expectExceptionMessage('Expected string value for ThreadId');

        $this->type->convertToPHPValue(123, $this->platform);
    }

    public function testConvertToDatabaseValueWithThreadId(): void
    {
        $threadId = new ThreadId('test-thread-id');
        $result = $this->type->convertToDatabaseValue($threadId, $this->platform);

        $this->assertEquals('test-thread-id', $result);
    }

    public function testConvertToDatabaseValueWithString(): void
    {
        $value = 'test-thread-id';
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
        $this->expectExceptionMessage('Expected ThreadId or string value');

        $this->type->convertToDatabaseValue(123, $this->platform);
    }

    public function testGetName(): void
    {
        $this->assertEquals('thr_id', $this->type->getName());
    }
}
