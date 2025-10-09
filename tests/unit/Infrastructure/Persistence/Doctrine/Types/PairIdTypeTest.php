<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Conversation\PairId;
use Chatbot\Infrastructure\Persistence\Doctrine\Types\PairIdType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class PairIdTypeTest extends TestCase
{
    private PairIdType $type;
    private AbstractPlatform&MockObject $platform;

    protected function setUp(): void
    {
        $this->type = new PairIdType();
        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    public function testGetName(): void
    {
        $this->assertEquals('pai_id', $this->type->getName());
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
        $pairId = new PairId('test-pair-id');
        $result = $this->type->convertToDatabaseValue($pairId, $this->platform);
        $this->assertEquals('test-pair-id', $result);
    }

    public function testConvertToPHPValue(): void
    {
        $value = 'test-pair-id';
        $result = $this->type->convertToPHPValue($value, $this->platform);

        $this->assertInstanceOf(PairId::class, $result);
        $this->assertEquals($value, $result->getId());
    }
}
