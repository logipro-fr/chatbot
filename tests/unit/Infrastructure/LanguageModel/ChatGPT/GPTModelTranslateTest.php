<?php

namespace Chatbot\Tests\Infrastructure\languageModel\ChatGPT;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\PairArray;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\GPTModelTranslate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function Safe\file_get_contents;

class GPTModelTranslateTest extends TestCase
{
    public function testGPTModelTranslate(): void
    {
        $conversation = new Conversation(new PairArray(), new ContextId("base"));
        $client = $this->createMockHttpClient('responseGETHello.json', 200);
        $service = new GPTModelTranslate($client, "english", $conversation);
        $message = $service->generateTextAnswer(new Prompt("Bonjour"));
        $this->assertEquals("Hello", $message->getMessage());
    }

    private function createMockHttpClient(string $filename, int $code): MockHttpClient
    {
        $responses = [
            new MockResponse(file_get_contents(__DIR__ . '/../../../ressources/' . $filename), ['http_code' => $code]),
        ];

        return new MockHttpClient($responses, 'https://api.openai.com/v1/chat/completion');
    }
}
