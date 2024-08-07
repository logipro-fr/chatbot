<?php

namespace Chatbot\Tests\Domain\Conversation;

use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\Pair;
use Chatbot\Domain\Model\Conversation\Prompt;
use PHPUnit\Framework\TestCase;

class PairTest extends TestCase
{
    public function testPair1(): void
    {
        $pair = new Pair(new Prompt("Bonjour"), new Answer("Comment puis-je vous aidez ?", 200));
        $pair2 = new Pair(new Prompt("Bonjour chatgpt"), new Answer("Comment puis-je vous aidez ?", 200));
        $this->assertFalse($pair === $pair2);
    }

    public function testCountToken(): void
    {
        $pair = new Pair(new Prompt("Bonjour"), new Answer("Comment puis-je vous aidez ?", 200));
        $this->assertEquals(6, $pair->countToken());
    }
}
