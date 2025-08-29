<?php

namespace Chatbot\Tests\Infrastructure\languageModel\ChatGPT;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\ChatbotGPTApi;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\RequestGPT;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function Safe\file_get_contents;

class ResponseGPTTest extends TestCase
{
    private ?string $savedChatbotApiKeyEnv = null;

    public function setUp(): void
    {
        $this->saveChatbotApiKeyEnv();
        $_ENV['CHATBOT_KEY_API'] = 'fake-api-key-for-testing';
    }

    protected function tearDown(): void
    {
        if ($this->savedChatbotApiKeyEnv !== null) {
            $_ENV['CHATBOT_API_KEY'] = $this->savedChatbotApiKeyEnv;
            $this->savedChatbotApiKeyEnv = null;
        }
    }

    private function saveChatbotApiKeyEnv(): void
    {
        $this->savedChatbotApiKeyEnv = null;
        if (isset($_ENV['CHATBOT_KEY_API'])) {
            $this->savedChatbotApiKeyEnv = $_ENV['CHATBOT_KEY_API'];
        }
    }

    public function testGetStatusCode(): void
    {
        $conversation = new Conversation(new ContextId("base"));
        $client = $this->createMockHttpClient('responseGETblague.json', 200);
        $prompt = new Prompt("raconte moi une blague stp");
        $context = new Context(new ContextMessage("You're helpfull assistant"));
        $chatBotTest = new ChatbotGPTApi($client);
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        $response = $chatBotTest->request($requestGPT);
        $this->assertEquals(200, $response->statusCode);
    }

    private function createMockHttpClient(string $filename, int $code): MockHttpClient
    {
        $responses = [
            new MockResponse(file_get_contents(__DIR__ . '/../../../ressources/' . $filename), ['http_code' => $code]),
        ];

        return new MockHttpClient($responses, 'https://api.openai.com/v1/chat/completion');
    }
}
