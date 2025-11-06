<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Infrastructure\Persistence\Doctrine\Types\ContextMessageType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ContextMessageTypeTest extends TestCase
{
    private ContextMessageType $type;
    private AbstractPlatform&MockObject $platform;

    protected function setUp(): void
    {
        $this->type = new ContextMessageType();
        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    public function testGetName(): void
    {
        $this->assertEquals('contextmessage', $this->type->getName());
    }

    public function testGetSQLDeclaration(): void
    {
        $column = ['length' => 255];
        $result = $this->type->getSQLDeclaration($column, $this->platform);
        $this->assertEquals('TEXT', $result);
    }

    public function testConvertToDatabaseValue(): void
    {
        $contextMessage = new ContextMessage('Test context message');
        $result = $this->type->convertToDatabaseValue($contextMessage, $this->platform);
        $this->assertEquals('Test context message', $result);
    }

    public function testConvertToPHPValue(): void
    {
        $value = 'Test context message';
        $result = $this->type->convertToPHPValue($value, $this->platform);

        $this->assertInstanceOf(ContextMessage::class, $result);
        $this->assertEquals($value, $result->getMessage());
    }
}
