<?php

namespace Chatbot\Tests\Infrastructure\languageModel\ChatGPT;

use Chatbot\Domain\Model\Conversation\Context;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\GPTModel;
use Chatbot\Tests\BaseTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function Safe\file_get_contents;

class GPTModelTest extends BaseTestCase
{
    private string $API_KEY;

    public function setUp(): void
    {

        parent::setUp();
        $apiKey = getenv('API_KEY');
        if ($apiKey === false) {
            var_dump(false);
            throw new \RuntimeException('API_KEY environment variable is not set.');
        } else {
            $this->API_KEY = $apiKey;
        }
    }

    public function testGPTModel(): void
    {
        $service = new GPTModel($this->createMockHttpClient('responseGETbonjour.json', 200), new Context("Your're helpfull assistant"), $this->API_KEY);
        $message = $service->generateTextAnswer(new Prompt("Bonjour"));
        $this->assertEquals("\n\nBonjour ! Je vais bien merci ! comment puis-je vous aidez aujourd'hui", $message->getMessage());
    }

    private function createMockHttpClient(string $filename, int $code): MockHttpClient
    {
        $responses = [
            new MockResponse(file_get_contents(__DIR__ . '/../../../ressources/' . $filename), ['http_code' => $code]),
        ];

        return new MockHttpClient($responses, 'https://api.openai.com/v1/chat/completion');
    }
}
