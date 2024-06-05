<?php

namespace Chatbot\Tests\Application\Service;

use Chatbot\Domain\Model\Conversation\Context;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\RequestGPT;
use PHPUnit\Framework\TestCase;

class requestGPTTest extends TestCase
{
    public function testRequestGPT(): void
    {
        $prompt = new Prompt("Bonjour");
        $context = new Context("You're helpfull assistant");
        $this->assertInstanceOf(RequestGPT::class, new RequestGPT($prompt, $context));
    }
}
