<?php

namespace Chatbot\Tests\Domain;

use Chatbot\Domain\Model\Conversation\Prompt;
use PHPUnit\Framework\TestCase;

class PromptTest extends TestCase
{
    public function testCountToken1(): void
    {
        $prompt = new Prompt("Bonjour");
        $this->assertEquals(1, $prompt->countToken());
    }

    public function testEqualsPrompt(): void
    {
        $a1 = new Prompt("Bonjour");
        $a2 = new Prompt("Bonjour");
        $this->assertTrue($a1->equals($a2));
    }

    public function testNotEqualsPrompt(): void
    {
        $a1 = new Prompt("Bonjour");
        $a2 = new Prompt("Bonjour chatgpt");
        $this->assertFalse($a1->equals($a2));
    }
}
