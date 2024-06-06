<?php

namespace Chatbot\Tests\Infrastructure\languageModel\ChatGPT;

use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\GPTModelTranslate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function Safe\file_get_contents;

class GPTModelTranslateTest extends TestCase
{
    private string $API_KEY ;

    public function setUp(): void
    {
        $apiKey = getenv('API_KEY');
        if ($apiKey === false) {
            throw new \RuntimeException('API_KEY environment variable is not set.');
        } else {
            $this->API_KEY = $apiKey;
        }
    }

    public function testGPTModelTranslate(): void
    {
        $service = new GPTModelTranslate($this->createMockHttpClient('responseGETHello.json', 200), $this->API_KEY, "english");
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
