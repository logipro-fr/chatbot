<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Doctrine\Types;


use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Infrastructure\Persistence\Doctrine\Types\ConversationIdType;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use PHPUnit\Framework\TestCase;

class ConversationIdTypeTest extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals('con_id', (new ConversationIdType())->getName());
    }

    public function testConvertToPHPValue(): void
    {
        $type = new ConversationIdType();
        $id = $type->convertToPHPValue("con_", new SqlitePlatform());
        $this->assertEquals(true, $id instanceof ConversationId);
    }

    public function testConvertToDatabaseValue(): void
    {
        $type = new ConversationIdType();
        $dbValue = $type->convertToDatabaseValue($id = new ConversationId(), new SqlitePlatform());
        $this->assertEquals($id->__toString(), $dbValue);
    }
}
