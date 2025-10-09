<?php

namespace Chatbot\Tests\Unit\Infrastructure\LanguageModel\ChatGPT\Assistant;

use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AssistantApiTest extends TestCase
{
    private ?string $savedChatbotApiKeyEnv = null;
    protected HttpClientInterface $client;

    public function setUp(): void
    {
        $this->saveChatbotApiKeyEnv();
        $this->client = $this->createMockHttpClient();
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

    protected function tearDown(): void
    {
        if ($this->savedChatbotApiKeyEnv !== null) {
            $_ENV['CHATBOT_KEY_API'] = $this->savedChatbotApiKeyEnv;
            $this->savedChatbotApiKeyEnv = null;
        }
    }

    public function testCreateAssistantWithoutFiles(): void
    {
        $name = 'Test Assistant';
        $instructions = 'Test instructions';
        $expectedAssistantId = 'asst-abc123';

        $response = new MockResponse((string) json_encode(['id' => $expectedAssistantId]), ['http_code' => 200]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->createAssistant($name, $instructions);

        $this->assertEquals($expectedAssistantId, $result);
    }

    public function testCreateAssistantWithFiles(): void
    {
        $name = 'Test Assistant';
        $instructions = 'Test instructions';
        $fileIds = ['file-123', 'file-456'];
        $expectedVectorStoreId = 'vs-xyz789';
        $expectedAssistantId = 'asst-abc123';

        $responses = [
            new MockResponse((string) json_encode(['id' => $expectedVectorStoreId]), ['http_code' => 200]),
            new MockResponse((string) json_encode(['id' => $expectedAssistantId]), ['http_code' => 200])
        ];

        $client = new MockHttpClient($responses);
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->createAssistant($name, $instructions, $fileIds);

        $this->assertEquals($expectedAssistantId, $result);
    }

    public function testCreateVectorStore(): void
    {
        $fileIds = ['file-123', 'file-456'];
        $expectedVectorStoreId = 'vs-xyz789';

        $response = new MockResponse((string) json_encode(['id' => $expectedVectorStoreId]), ['http_code' => 200]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/vector_stores');
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->createVectorStore($fileIds);

        $this->assertEquals($expectedVectorStoreId, $result);
    }

    public function testGetVectorStore(): void
    {
        $vectorStoreId = 'vs-xyz789';
        $expectedData = [
            'id' => $vectorStoreId,
            'name' => 'Test Vector Store',
            'status' => 'completed'
        ];

        $response = new MockResponse((string) json_encode($expectedData), ['http_code' => 200]);
        $client = new MockHttpClient($response, "https://api.openai.com/v1/vector_stores/{$vectorStoreId}");
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->getVectorStore($vectorStoreId);

        $this->assertEquals($expectedData, $result);
    }

    public function testGetVectorStoreFiles(): void
    {
        $vectorStoreId = 'vs-xyz789';
        $expectedData = [
            ['id' => 'file-123', 'status' => 'completed'],
            ['id' => 'file-456', 'status' => 'completed']
        ];

        $response = new MockResponse((string) json_encode(['data' => $expectedData]), ['http_code' => 200]);
        $client = new MockHttpClient($response, "https://api.openai.com/v1/vector_stores/{$vectorStoreId}/files");
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->getVectorStoreFiles($vectorStoreId);

        $this->assertEquals($expectedData, $result);
    }

    public function testDeleteVectorStore(): void
    {
        $vectorStoreId = 'vs-xyz789';

        $response = new MockResponse('', ['http_code' => 200]);
        $client = new MockHttpClient($response, "https://api.openai.com/v1/vector_stores/{$vectorStoreId}");
        $assistantApi = new AssistantApi($client);

        $assistantApi->deleteVectorStore($vectorStoreId);

        $this->addToAssertionCount(1);
    }

    public function testGetAssistant(): void
    {
        $assistantId = 'asst-abc123';
        $expectedData = [
            'id' => $assistantId,
            'object' => 'assistant',
            'created_at' => 1704110400,
            'name' => 'Test Assistant',
            'model' => 'gpt-4-turbo',
            'instructions' => 'Test instructions',
            'tools' => [
                ['type' => 'file_search']
            ],
            'tool_resources' => [
                'file_search' => [
                    'vector_store_ids' => ['vs-123']
                ]
            ]
        ];

        $response = new MockResponse((string) json_encode($expectedData), ['http_code' => 200]);
        $client = new MockHttpClient($response, "https://api.openai.com/v1/assistants/{$assistantId}");
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->getAssistant($assistantId);

        $this->assertEquals($expectedData, $result);
    }


    public function testBadRequest(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("OpenAI API Error: {\"error\":{\"message\":\"Invalid request\"}}");

        $response = new MockResponse('{"error":{"message":"Invalid request"}}', ['http_code' => 400]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
        $assistantApi = new AssistantApi($client);

        $assistantApi->createAssistant('Test', 'Instructions');
    }

    public function testUnauthorized(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("OpenAI API Error: {\"error\":{\"message\":\"Unauthorized\"}}");

        $response = new MockResponse('{"error":{"message":"Unauthorized"}}', ['http_code' => 401]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
        $assistantApi = new AssistantApi($client);

        $assistantApi->createAssistant('Test', 'Instructions');
    }

    private function createMockHttpClient(): MockHttpClient
    {
        $response = new MockResponse('{"id":"asst-test"}', ['http_code' => 200]);
        return new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
    }
}
