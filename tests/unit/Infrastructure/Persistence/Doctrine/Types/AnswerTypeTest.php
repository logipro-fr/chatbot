<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Infrastructure\Persistence\Doctrine\Types\AnswerType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AnswerTypeTest extends TestCase
{
    private AnswerType $type;
    private AbstractPlatform&MockObject $platform;

    protected function setUp(): void
    {
        $this->type = new AnswerType();
        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    public function testGetName(): void
    {
        $this->assertEquals('answer', $this->type->getName());
    }

    public function testGetSQLDeclaration(): void
    {
        $column = ['length' => 255];
        $result = $this->type->getSQLDeclaration($column, $this->platform);
        $this->assertEquals('TEXT', $result);
    }

    public function testConvertToDatabaseValue(): void
    {
        $answer = new Answer('Test message', 200);
        $result = $this->type->convertToDatabaseValue($answer, $this->platform);
        $this->assertEquals('Test message', $result);
    }

    public function testConvertToPHPValue(): void
    {
        $value = 'Test message';
        $result = $this->type->convertToPHPValue($value, $this->platform);

        $this->assertInstanceOf(Answer::class, $result);
        $this->assertEquals('Test message', $result->getMessage());
        $this->assertEquals(200, $result->getCodeStatus());
    }
}
