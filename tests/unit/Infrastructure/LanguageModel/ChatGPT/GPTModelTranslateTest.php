<?php

namespace Chatbot\Tests\Infrastructure\languageModel\ChatGPT;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\GPTModelTranslate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function Safe\file_get_contents;

class GPTModelTranslateTest extends TestCase
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
            $envValue = $_ENV['CHATBOT_KEY_API'];
            if (is_string($envValue)) {
                $this->savedChatbotApiKeyEnv = $envValue;
            }
        }
    }

    public function testGPTModelTranslate(): void
    {
        $conversation = new Conversation(new ContextId("base"));
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
