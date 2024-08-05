<?php

namespace Chatbot\Tests\Infrastructure\languageModel\ChatGPT;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\ChatbotGPTApi;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\RequestGPT;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function Safe\file_get_contents;

class ResponseGPTTest extends TestCase
{
    public function testGetStatusCode(): void
    {
        $client = $this->createMockHttpClient('responseGETblague.json', 200);
        $prompt = new Prompt("raconte moi une blague stp");
        $context = new Context(new ContextMessage("You're helpfull assistant"));
        $chatBotTest = new ChatbotGPTApi($client);
        $requestGPT = new RequestGPT($prompt, $context);
        $response = $chatBotTest->request($requestGPT);
        $this->assertEquals(true, is_int($response->statusCode));
    }

    private function createMockHttpClient(string $filename, int $code): MockHttpClient
    {
        $responses = [
            new MockResponse(file_get_contents(__DIR__ . '/../../../ressources/' . $filename), ['http_code' => $code]),
        ];

        return new MockHttpClient($responses, 'https://api.openai.com/v1/chat/completion');
    }
}
