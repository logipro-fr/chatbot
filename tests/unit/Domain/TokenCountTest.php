<?php

namespace Chatbot\Tests\Domain;

use Chatbot\Domain\Model\Conversation\TokenCount;
use PHPUnit\Framework\TestCase;

class TokenCountTest extends TestCase
{
    public function testTokenCount(): void
    {
        $tokenCount = new TokenCount();
        $prompt = "Hello fri3nd, you're
        looking          good today!";
        $this->assertEquals(7, $tokenCount->countToken($prompt));
    }


    public function testCountToken2(): void
    {
        $tokenCount = new TokenCount();
        $prompt = "aujourd'hui";
        $this->assertEquals(2, $tokenCount->countToken($prompt));
    }


    public function testCountToken3(): void
    {
        $tokenCount = new TokenCount();
        $prompt = "\n\nBonjour ! Je vais bien merci ! comment puis-je vous aidez aujourd'hui";
        $this->assertEquals(12, $tokenCount->countToken($prompt));
    }
}
