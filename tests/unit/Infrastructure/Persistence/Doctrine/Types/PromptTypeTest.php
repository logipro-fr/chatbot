<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\Persistence\Doctrine\Types\PromptType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class PromptTypeTest extends TestCase
{
    private PromptType $type;
    private AbstractPlatform&MockObject $platform;

    protected function setUp(): void
    {
        $this->type = new PromptType();
        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    public function testGetName(): void
    {
        $this->assertEquals('prompt', $this->type->getName());
    }

    public function testGetSQLDeclaration(): void
    {
        $column = ['length' => 255];
        $result = $this->type->getSQLDeclaration($column, $this->platform);
        $this->assertEquals('TEXT', $result);
    }

    public function testConvertToDatabaseValue(): void
    {
        $prompt = new Prompt('Test user request');
        $result = $this->type->convertToDatabaseValue($prompt, $this->platform);
        $this->assertEquals('Test user request', $result);
    }

    public function testConvertToPHPValue(): void
    {
        $value = 'Test user request';
        $result = $this->type->convertToPHPValue($value, $this->platform);

        $this->assertInstanceOf(Prompt::class, $result);
        $this->assertEquals($value, $result->getUserResquest());
    }
}
