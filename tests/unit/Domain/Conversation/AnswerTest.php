<?php

namespace Chatbot\Tests\Domain\Conversation;

use Chatbot\Domain\Model\Conversation\Answer;
use PHPUnit\Framework\TestCase;

class AnswerTest extends TestCase
{
    public function testAnswer(): void
    {
        $answer = new Answer("Oui, ca va", 200);
        $this->assertEquals("Oui, ca va", $answer->getMessage());
    }

    public function testCountToken1(): void
    {
        $answer = new Answer("Bonjour", 200);
        $this->assertEquals(1, $answer->countToken());
    }

    public function testEqualsAnswer(): void
    {
        $a1 = new Answer("Bonjour", 200);
        $a2 = new Answer("Bonjour", 200);
        $this->assertTrue($a1->equals($a2));
    }

    public function testNotEqualsAnswer(): void
    {
        $a1 = new Answer("Bonjour", 200);
        $a2 = new Answer("Bonjour chatgpt", 200);
        $this->assertFalse($a1->equals($a2));
    }

    public function testCodeStatue(): void
    {
        $a1 = new Answer("Bonjour", 200);
        $this->assertEquals(200, $a1->getCodeStatus());
    }
}
