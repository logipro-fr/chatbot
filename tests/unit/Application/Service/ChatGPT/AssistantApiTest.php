<?php

namespace Chatbot\Tests\Unit\Application\Service\ChatGPT;

use Chatbot\Application\Service\ChatGPT\AssistantApi;
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

    public function testCreateThread(): void
    {
        $expectedThreadId = 'thread-abc123';

        $response = new MockResponse((string) json_encode(['id' => $expectedThreadId]), ['http_code' => 200]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/threads');
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->createThread();

        $this->assertEquals($expectedThreadId, $result);
    }

    public function testCreateThreadWithMissingId(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid response: missing thread ID');

        $response = new MockResponse('{}', ['http_code' => 200]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/threads');
        $assistantApi = new AssistantApi($client);

        $assistantApi->createThread();
    }

    public function testAddMessageToThread(): void
    {
        $threadId = 'thread-abc123';
        $content = 'Hello, world!';
        $expectedMessageId = 'msg-xyz789';

        $response = new MockResponse((string) json_encode(['id' => $expectedMessageId]), ['http_code' => 200]);
        $client = new MockHttpClient($response, "https://api.openai.com/v1/threads/{$threadId}/messages");
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->addMessageToThread($threadId, $content);

        $this->assertEquals($expectedMessageId, $result);
    }

    public function testAddMessageToThreadWithMissingId(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid response: missing message ID');

        $response = new MockResponse('{}', ['http_code' => 200]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/threads/thread-123/messages');
        $assistantApi = new AssistantApi($client);

        $assistantApi->addMessageToThread('thread-123', 'test message');
    }

    public function testCreateRun(): void
    {
        $threadId = 'thread-abc123';
        $assistantId = 'asst-xyz789';
        $expectedRunId = 'run-def456';

        // Mock pour getAssistant (appelé avant createRun)
        $assistantData = [
            'id' => $assistantId,
            'tool_resources' => null
        ];
        $responses = [
            new MockResponse((string) json_encode($assistantData), ['http_code' => 200]),
            new MockResponse((string) json_encode(['id' => $expectedRunId]), ['http_code' => 200])
        ];
        $client = new MockHttpClient($responses);
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->createRun($threadId, $assistantId);

        $this->assertEquals($expectedRunId, $result);
    }

    public function testCreateRunWithMissingId(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid response: missing run ID');

        // Mock pour getAssistant (appelé avant createRun)
        $assistantData = [
            'id' => 'asst-123',
            'tool_resources' => null
        ];
        $responses = [
            new MockResponse((string) json_encode($assistantData), ['http_code' => 200]),
            new MockResponse('{}', ['http_code' => 200])
        ];
        $client = new MockHttpClient($responses);
        $assistantApi = new AssistantApi($client);

        $assistantApi->createRun('thread-123', 'asst-123');
    }

    public function testCreateRunWithFiles(): void
    {
        $threadId = 'thread-abc123';
        $assistantId = 'asst-xyz789';
        $expectedRunId = 'run-def456';

        // Mock pour getAssistant avec fichiers attachés
        $assistantData = [
            'id' => $assistantId,
            'tool_resources' => [
                'file_search' => [
                    'vector_store_ids' => ['vs-123']
                ]
            ]
        ];
        $responses = [
            new MockResponse((string) json_encode($assistantData), ['http_code' => 200]),
            new MockResponse((string) json_encode(['id' => $expectedRunId]), ['http_code' => 200])
        ];
        $client = new MockHttpClient($responses);
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->createRun($threadId, $assistantId);

        $this->assertEquals($expectedRunId, $result);
    }

    public function testGetRunStatus(): void
    {
        $threadId = 'thread-abc123';
        $runId = 'run-xyz789';
        $expectedData = [
            'id' => $runId,
            'status' => 'completed',
            'assistant_id' => 'asst-123'
        ];

        $response = new MockResponse((string) json_encode($expectedData), ['http_code' => 200]);
        $client = new MockHttpClient($response, "https://api.openai.com/v1/threads/{$threadId}/runs/{$runId}");
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->getRunStatus($threadId, $runId);

        $this->assertEquals($expectedData, $result);
    }

    public function testGetMessages(): void
    {
        $threadId = 'thread-abc123';
        $expectedData = [
            ['id' => 'msg-1', 'role' => 'user', 'content' => 'Hello'],
            ['id' => 'msg-2', 'role' => 'assistant', 'content' => 'Hi there!']
        ];

        $response = new MockResponse((string) json_encode(['data' => $expectedData]), ['http_code' => 200]);
        $client = new MockHttpClient($response, "https://api.openai.com/v1/threads/{$threadId}/messages");
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->getMessages($threadId);

        $this->assertEquals($expectedData, $result);
    }

    public function testGetMessagesWithNonArrayData(): void
    {
        $threadId = 'thread-abc123';

        $response = new MockResponse('{"data": "not-an-array"}', ['http_code' => 200]);
        $client = new MockHttpClient($response, "https://api.openai.com/v1/threads/{$threadId}/messages");
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->getMessages($threadId);

        $this->assertEmpty($result);
    }

    public function testGetMessagesWithMixedData(): void
    {
        $threadId = 'thread-abc123';
        $responseData = [
            'data' => [
                ['id' => 'msg-1', 'role' => 'user'],
                'not-an-array',
                ['id' => 'msg-2', 'role' => 'assistant']
            ]
        ];

        $response = new MockResponse((string) json_encode($responseData), ['http_code' => 200]);
        $client = new MockHttpClient($response, "https://api.openai.com/v1/threads/{$threadId}/messages");
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->getMessages($threadId);

        $this->assertCount(2, $result);
        $this->assertEquals('msg-1', $result[0]['id']);
        $this->assertEquals('msg-2', $result[1]['id']);
    }

    public function testDeleteAssistant(): void
    {
        $assistantId = 'asst-abc123';

        $response = new MockResponse('', ['http_code' => 200]);
        $client = new MockHttpClient($response, "https://api.openai.com/v1/assistants/{$assistantId}");
        $assistantApi = new AssistantApi($client);

        $assistantApi->deleteAssistant($assistantId);

        $this->addToAssertionCount(1);
    }

    public function testValidateFileIdsWithEmptyId(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid file ID: ');

        $response = new MockResponse('{"id":"asst-test"}', ['http_code' => 200]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
        $assistantApi = new AssistantApi($client);

        $assistantApi->createAssistant('Test', 'Instructions', ['']);
    }

    public function testValidateFileIdsWithInvalidFormat(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid file ID format: invalid-id. Expected format: file-xxxxx');

        $response = new MockResponse('{"id":"asst-test"}', ['http_code' => 200]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
        $assistantApi = new AssistantApi($client);

        $assistantApi->createAssistant('Test', 'Instructions', ['invalid-id']);
    }

    public function testConstructorWithMissingEnvVariable(): void
    {
        $originalEnv = $_ENV['CHATBOT_KEY_API'] ?? null;
        unset($_ENV['CHATBOT_KEY_API']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Missing environment variable: CHATBOT_KEY_API is required to initialize AssistantApi.'
        );

        new AssistantApi($this->client);

        if ($originalEnv !== null) {
            $_ENV['CHATBOT_KEY_API'] = $originalEnv;
        }
    }

    public function testConstructorWithNonStringEnvValue(): void
    {
        $originalEnv = $_ENV['CHATBOT_KEY_API'] ?? null;
        $_ENV['CHATBOT_KEY_API'] = 123;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Environment variable CHATBOT_KEY_API must be a string.');

        new AssistantApi($this->client);

        if ($originalEnv !== null) {
            $_ENV['CHATBOT_KEY_API'] = $originalEnv;
        } else {
            unset($_ENV['CHATBOT_KEY_API']);
        }
    }

    public function testHandleResponseWithUnauthorized(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unauthorized: Invalid or missing API key.');

        $response = $this->createMock(\Symfony\Contracts\HttpClient\ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(401);
        $response->method('getContent')->willReturn('{"error": "Unauthorized"}');

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);

        $assistantApi = new AssistantApi($client);
        $assistantApi->getAssistant('asst-123');
    }

    public function testHandleResponseWithBadRequestWithMessage(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad Request: Invalid request data');

        $response = $this->createMock(\Symfony\Contracts\HttpClient\ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);
        $response->method('getContent')->willReturn('{"error": {"message": "Invalid request data"}}');

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);

        $assistantApi = new AssistantApi($client);
        $assistantApi->getAssistant('asst-123');
    }

    public function testHandleResponseWithBadRequestWithoutMessage(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad Request: {"error": "Bad request"}');

        $response = $this->createMock(\Symfony\Contracts\HttpClient\ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);
        $response->method('getContent')->willReturn('{"error": "Bad request"}');

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);

        $assistantApi = new AssistantApi($client);
        $assistantApi->getAssistant('asst-123');
    }

    public function testHandleResponseWithBadRequestWithNonStringMessage(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad Request: Unknown error');

        $response = $this->createMock(\Symfony\Contracts\HttpClient\ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);
        $response->method('getContent')->willReturn('{"error": {"message": 123}}');

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);

        $assistantApi = new AssistantApi($client);
        $assistantApi->getAssistant('asst-123');
    }

    public function testHandleResponseWithTooManyRequests(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Too Many Requests: You have exceeded your request quota.');

        $response = $this->createMock(\Symfony\Contracts\HttpClient\ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(429);
        $response->method('getContent')->willReturn('{"error": "Rate limit exceeded"}');

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);

        $assistantApi = new AssistantApi($client);
        $assistantApi->getAssistant('asst-123');
    }

    public function testHandleResponseWithOtherError(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Unexpected error: received HTTP status code 500. {"error": "Internal Server Error"}'
        );

        $response = $this->createMock(\Symfony\Contracts\HttpClient\ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);
        $response->method('getContent')->willReturn('{"error": "Internal Server Error"}');

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);

        $assistantApi = new AssistantApi($client);
        $assistantApi->getAssistant('asst-123');
    }

    public function testCreateVectorStoreWithError(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('OpenAI Vector Store API Error: {"error": "Vector store creation failed"}');

        $response = new MockResponse('{"error": "Vector store creation failed"}', ['http_code' => 400]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/vector_stores');
        $assistantApi = new AssistantApi($client);

        $assistantApi->createVectorStore(['file-123']);
    }

    public function testGetVectorStoreFilesWithNonArrayData(): void
    {
        $vectorStoreId = 'vs-xyz789';

        $response = new MockResponse('{"data": "not-an-array"}', ['http_code' => 200]);
        $client = new MockHttpClient($response, "https://api.openai.com/v1/vector_stores/{$vectorStoreId}/files");
        $assistantApi = new AssistantApi($client);

        $result = $assistantApi->getVectorStoreFiles($vectorStoreId);

        $this->assertEmpty($result);
    }

    public function testParamsHeaderWithBodyData(): void
    {
        $testFileApi = new class ($this->client, 'test-api-key') extends AssistantApi {
            /** @return array<string, array<string, string>|array<string, string|int|bool>> */
            public function testParamsHeaderWithBody(): array
            {
                $reflection = new \ReflectionClass($this);
                $method = $reflection->getMethod('paramsHeader');
                $method->setAccessible(true);
                $result = $method->invoke($this, ['key' => 'value'], false);
                assert(is_array($result));
                /** @var array<string, array<string, string>|array<string, string|int|bool>> $result */
                return $result;
            }
        };

        $result = $testFileApi->testParamsHeaderWithBody();

        $this->assertArrayHasKey('headers', $result);
        $this->assertArrayHasKey('body', $result);
        $this->assertEquals(['key' => 'value'], $result['body']);
        $this->assertArrayNotHasKey('Content-Type', $result['headers']);
    }

    private function createMockHttpClient(): MockHttpClient
    {
        $response = new MockResponse('{"id":"asst-test"}', ['http_code' => 200]);
        return new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
    }
}
