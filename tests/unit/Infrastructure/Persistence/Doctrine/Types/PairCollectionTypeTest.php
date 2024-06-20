<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\Pair;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\Persistence\Doctrine\Types\PairCollectionType;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use PHPUnit\Framework\TestCase;

class PairCollectionTypeTest extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals('pairs', (new PairCollectionType())->getName());
    }

    public function testConvertValue(): void
    {
        $type = new PairCollectionType();
        $dbValue = $type->convertToDatabaseValue(
            $pair = new Pair(new Prompt("Bonjour"), new Answer("Comment aller vous ?", 200)),
            new SqlitePlatform()
        );
        $this->assertIsString($dbValue);

        $phpValue = $type->convertToPHPValue($dbValue, new SqlitePlatform());
        $this->assertEquals($pair, $phpValue);
    }
}
