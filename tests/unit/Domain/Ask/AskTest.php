<?php

namespace Chatbot\Tests\Domain\Service\Ask;

use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Domain\Service\Ask\Ask;
use Chatbot\Infrastructure\LanguageModel\Parrot;
use PHPUnit\Framework\TestCase;

class AskTest extends TestCase
{
    public function testAsk(): void
    {
        $service = new Ask();
        $answer = $service->execute(new Prompt("Bonjour"), new Parrot());
        $message = $answer->getMessage();
        $this->assertEquals("Bonjour", $answer->getMessage());
    }
}
