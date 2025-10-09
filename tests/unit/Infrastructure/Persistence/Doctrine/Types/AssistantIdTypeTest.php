<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Persistence\Doctrine\Types\AssistantIdType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AssistantIdTypeTest extends TestCase
{
    private AssistantIdType $type;
    private AbstractPlatform&MockObject $platform;

    protected function setUp(): void
    {
        $this->type = new AssistantIdType();
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
        $value = 'test-assistant-id';
        $result = $this->type->convertToPHPValue($value, $this->platform);

        $this->assertInstanceOf(AssistantId::class, $result);
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
        $this->expectExceptionMessage('Expected string value for AssistantId');

        $this->type->convertToPHPValue(123, $this->platform);
    }

    public function testConvertToDatabaseValueWithAssistantId(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $result = $this->type->convertToDatabaseValue($assistantId, $this->platform);

        $this->assertEquals('test-assistant-id', $result);
    }

    public function testConvertToDatabaseValueWithString(): void
    {
        $value = 'test-assistant-id';
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
        $this->expectExceptionMessage('Expected AssistantId or string value');

        $this->type->convertToDatabaseValue(123, $this->platform);
    }

    public function testGetName(): void
    {
        $this->assertEquals('ast_id', $this->type->getName());
    }
}
