<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Infrastructure\Persistence\Doctrine\Types\ContextIdType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ContextIdTypeTest extends TestCase
{
    private ContextIdType $type;
    private AbstractPlatform&MockObject $platform;

    protected function setUp(): void
    {
        $this->type = new ContextIdType();
        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    public function testGetName(): void
    {
        $this->assertEquals('cot_id', $this->type->getName());
    }

    public function testGetSQLDeclaration(): void
    {
        $column = ['length' => 36];
        $this->platform->expects($this->once())
            ->method('getGuidTypeDeclarationSQL')
            ->with($column)
            ->willReturn('CHAR(36)');

        $result = $this->type->getSQLDeclaration($column, $this->platform);
        $this->assertEquals('CHAR(36)', $result);
    }

    public function testConvertToDatabaseValue(): void
    {
        $contextId = new ContextId('test-context-id');
        $result = $this->type->convertToDatabaseValue($contextId, $this->platform);
        $this->assertEquals('test-context-id', $result);
    }

    public function testConvertToPHPValue(): void
    {
        $value = 'test-context-id';
        $result = $this->type->convertToPHPValue($value, $this->platform);

        $this->assertInstanceOf(ContextId::class, $result);
        $this->assertEquals($value, $result->getId());
    }
}
