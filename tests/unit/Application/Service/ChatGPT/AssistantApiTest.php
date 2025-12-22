<?php

namespace Chatbot\Tests\Unit\Application\Service\ChatGPT;

use Chatbot\Application\Service\ChatGPT\AssistantApi;
use Chatbot\Application\Service\Exception\BadRequestException;
use Chatbot\Domain\Model\Assistant\Assistant;
use Doctrine\ORM\Query\Expr\Func;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\Test\TestStatus\Success;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function PHPUnit\Framework\stringContains;

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

    public function testCreateVectorStoreCallsSetVectorIdWhenAssistantProvided(): void
    {
        $fileIds = ['file-123', 'file-456'];
        $expectedVectorStoreId = 'vs-xyz789';

        $response = new MockResponse(
            (string) json_encode(['id' => $expectedVectorStoreId]),
            ['http_code' => 200]
        );
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/vector_stores');

        $assistantApi = new AssistantApi($client);

        $assistant = $this->createMock(Assistant::class);
        $assistant->expects($this->once())
        ->method('setVectorId')
        ->with($expectedVectorStoreId);

        $result = $assistantApi->createVectorStore($fileIds, $assistant);

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

    public function testCreateVectorStoreFile(): void
    {
        $vectorStoreId = 'vs-abc123';
        $filesIds = ['file-123', 'file-456'];

        $responses = [
        new MockResponse((string)json_encode(['data' => $filesIds]), ['http_code' => 200]),
        new MockResponse((string)json_encode(['data' => $filesIds]), ['http_code' => 200]),
        ];
        $client = new MockHttpClient($responses, "https://api.openai.com/v1/vector_stores/{$vectorStoreId}/files");

        $assistantApi = new AssistantApi($client);
        $assistantApi->createVectorStoreFile($vectorStoreId, $filesIds);

        $this->addToAssertionCount(1);
    }

    public function testCreateVectorStoreFileWithMissingId(): void
    {
        $vectorStoreId = 'vs-abc123';
        $filesIds = ['file-123', 'file-456'];

        $responses = [
        new MockResponse((string)json_encode(['data' => $filesIds]), ['http_code' => 400]),
        new MockResponse((string)json_encode(['data' => $filesIds]), ['http_code' => 400]),
        ];

        $client = new MockHttpClient($responses, "https://api.openai.com/v1/vector_stores/{$vectorStoreId}/files");

        $assistantApi = new AssistantApi($client);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('OpenAI Vector Store File API Error:');


        $assistantApi->createVectorStoreFile('', $filesIds);
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

    public function testAttachAssistantFileWhenFileIdsEmpty(): void
    {
        $externalAssistantId = 'asst-abc123';
        $assistant = $this->createMock(Assistant::class);

        /** @var list<array{method:string,url:string,options:array<string,mixed>}> $requests */
        $requests = [];

        $responses = [
        new MockResponse(json_encode(['ok' => true], JSON_THROW_ON_ERROR), ['http_code' => 200]),
        ];

        $client = new MockHttpClient(function (
            string $method,
            string $url,
            array $options
        ) use (
            &$requests,
            &$responses
        ): MockResponse {
            $requests[] = ['method' => $method, 'url' => $url, 'options' => $options];

            $response = array_shift($responses);
            self::assertInstanceOf(MockResponse::class, $response);

            return $response;
        });

        $assistantApi = new AssistantApi($client);

        $assistantApi->updateAssistantFile($externalAssistantId, $assistant, [], null);

        self::assertCount(1, $requests);

        $req = $requests[0];
        self::assertSame('POST', $req['method']);
        self::assertSame("https://api.openai.com/v1/assistants/{$externalAssistantId}", $req['url']);

        $options = $req['options'];

        $payload = $options['json'] ?? null;
        if (!is_array($payload)) {
            $rawBody = $options['body'] ?? '{}';
            self::assertIsString($rawBody);
            /** @var mixed $decoded */
            $decoded = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
            $payload = $decoded;
        }

        self::assertIsArray($payload);
        /** @var array<string, mixed> $payload */

        self::assertSame([], $payload['tools'] ?? null);
        self::assertSame([], $payload['tool_resources'] ?? null);
    }

    public function testAttachAssistantFileCreatesVectorStoreWhenVectorIdIsNull(): void
    {
        $externalAssistantId = 'asst-abc123';
        $assistant = $this->createMock(Assistant::class);
        $fileIds = ['file-123'];

        $createdVectorStoreId = 'vs-created-999';

        /** @var list<array{method:string,url:string,options:array<string,mixed>}> $requests */
        $requests = [];

        $responses = [
        new MockResponse(json_encode(['id' => $createdVectorStoreId], JSON_THROW_ON_ERROR), ['http_code' => 200]),
        new MockResponse(json_encode(['ok' => true], JSON_THROW_ON_ERROR), ['http_code' => 200]),
        ];

        $client = new MockHttpClient(function (string $method, string $url, array $options)
 use (&$requests, &$responses): MockResponse {
            $requests[] = ['method' => $method, 'url' => $url, 'options' => $options];

            $response = array_shift($responses);
            self::assertInstanceOf(MockResponse::class, $response);

            return $response;
        });

        $assistantApi = new AssistantApi($client);

        $assistantApi->updateAssistantFile($externalAssistantId, $assistant, $fileIds, null);

        self::assertCount(2, $requests);

        $createVsReq = $requests[0];
        self::assertSame('POST', $createVsReq['method']);
        self::assertStringContainsString('/v1/vector_stores', $createVsReq['url']);

        $createVsOptions = $createVsReq['options'];
        $createVsPayload = $createVsOptions['json'] ?? null;

        if (!is_array($createVsPayload)) {
            $rawBody = $createVsOptions['body'] ?? '{}';
            self::assertIsString($rawBody);
            $createVsPayload = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        }

        self::assertIsArray($createVsPayload);
        /** @var array<string, mixed> $createVsPayload */

        self::assertSame($fileIds, $createVsPayload['file_ids'] ?? null);

        $updateReq = $requests[1];
        self::assertSame('POST', $updateReq['method']);
        self::assertSame("https://api.openai.com/v1/assistants/{$externalAssistantId}", $updateReq['url']);

        $updateOptions = $updateReq['options'];
        $updatePayload = $updateOptions['json'] ?? null;

        if (!is_array($updatePayload)) {
            $rawBody = $updateOptions['body'] ?? '{}';
            self::assertIsString($rawBody);
            $updatePayload = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        }

        self::assertIsArray($updatePayload);
        /** @var array<string, mixed> $updatePayload */

        $tools = $updatePayload['tools'] ?? null;
        self::assertIsArray($tools);
        /** @var array<int, array<string, mixed>> $tools */

        self::assertSame('file_search', $tools[0]['type'] ?? null);

        $toolResources = $updatePayload['tool_resources'] ?? null;
        self::assertIsArray($toolResources);
        /** @var array<string, mixed> $toolResources */

        $fileSearch = $toolResources['file_search'] ?? null;
        self::assertIsArray($fileSearch);
        /** @var array<string, mixed> $fileSearch */

        self::assertSame([$createdVectorStoreId], $fileSearch['vector_store_ids'] ?? null);
    }

    public function testAttachAssistantFileUsesExistingVectorStoreWhenVectorIdProvided(): void
    {
        $externalAssistantId = 'asst-abc123';
        $assistant = $this->createMock(Assistant::class);
        $fileIds = ['file-123'];
        $vectorStoreId = 'vs-abc123';

        /** @var list<array{method:string,url:string,options:array<string,mixed>}> $requests */
        $requests = [];

        $responses = [
            new MockResponse(json_encode(['ok' => true], JSON_THROW_ON_ERROR), ['http_code' => 200]),
            new MockResponse(json_encode(['ok' => true], JSON_THROW_ON_ERROR), ['http_code' => 200]),
        ];

        $client = new MockHttpClient(function (
            string $method,
            string $url,
            array $options
        ) use (
            &$requests,
            &$responses
        ): MockResponse {
            $requests[] = ['method' => $method, 'url' => $url, 'options' => $options];

            $response = array_shift($responses);
            self::assertInstanceOf(MockResponse::class, $response);

            return $response;
        });

        $assistantApi = new AssistantApi($client);

        $assistantApi->updateAssistantFile($externalAssistantId, $assistant, $fileIds, $vectorStoreId);

        self::assertCount(2, $requests);

        $firstReq = $requests[0];
        self::assertSame('POST', $firstReq['method']);
        self::assertStringContainsString(
            "/v1/vector_stores/{$vectorStoreId}",
            $firstReq['url']
        );

        $secondReq = $requests[1];
        self::assertSame('POST', $secondReq['method']);
        self::assertSame(
            "https://api.openai.com/v1/assistants/{$externalAssistantId}",
            $secondReq['url']
        );

        $options = $secondReq['options'];

        $payload = $options['json'] ?? null;
        if (!is_array($payload)) {
            $rawBody = $options['body'] ?? '{}';
            self::assertIsString($rawBody);
            /** @var mixed $decoded */
            $decoded = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
            $payload = $decoded;
        }

        self::assertIsArray($payload);
        /** @var array<string, mixed> $payload */

        $toolResources = $payload['tool_resources'] ?? null;
        self::assertIsArray($toolResources);
        /** @var array<string, mixed> $toolResources */

        $fileSearch = $toolResources['file_search'] ?? null;
        self::assertIsArray($fileSearch);
        /** @var array<string, mixed> $fileSearch */

        self::assertSame([$vectorStoreId], $fileSearch['vector_store_ids'] ?? null);
    }

    public function testAttachAssistantException(): void
    {
        $externalAssistantId = 'asst-abc123';
        $assistant = $this->createMock(Assistant::class);
        $fileIds = ['file-123'];
        $vectorStoreId = 'vs-abc123';

        $responses = [
            new MockResponse(
                json_encode(['ok' => true], JSON_THROW_ON_ERROR),
                ['http_code' => 200]
            ),

            new MockResponse(
                json_encode(['ok' => false], JSON_THROW_ON_ERROR),
                ['http_code' => 400]
            ),
        ];

        $client = new MockHttpClient(static function () use (&$responses): MockResponse {
            $response = array_shift($responses);
            self::assertInstanceOf(MockResponse::class, $response);
            return $response;
        });

        $assistantApi = new AssistantApi($client);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('OpenAI API Error:');

        $assistantApi->updateAssistantFile($externalAssistantId, $assistant, $fileIds, $vectorStoreId);
    }

    public function testDetachAssistantFile(): void
    {
        $vectorStoreId = 'vs-abc123';
        $fileId = 'file-123';

        $response = new MockResponse('', ['http_code' => 200]);
        $client = new MockHttpClient($response, "https://api.openai.com/v1/vector_stores/{$vectorStoreId}/files/{$fileId}");

        $assistantApi = new AssistantApi(($client));

        $assistantApi->deleteVectorStoreFile($vectorStoreId, $fileId);

        $this->assertTrue(true);
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

    public function testUpdateAssistant(): void
    {
        $assistantId = 'asst-abc123';
        $name = 'Updated Assistant Name';
        $instructions = 'Updated instructions';

        // Réponse attendue de l'API
        $expectedResponse = [
           'id' => $assistantId,
           'name' => $name,
           'instructions' => $instructions,
        ];

        // Mock de l'API
        $response = new MockResponse(
            (string) json_encode($expectedResponse),
            ['http_code' => 200]
        );

        $client = new MockHttpClient(
            $response,
            "https://api.openai.com/v1/assistants/{$assistantId}"
        );

        $assistantApi = new AssistantApi($client);

        // Appel à la méthode updateAssistant
        $result = $assistantApi->updateAssistant($assistantId, $name, $instructions);

        // Vérifications
        $this->assertEquals($expectedResponse['id'], $result['id']);
        $this->assertEquals($expectedResponse['name'], $result['name']);
        $this->assertEquals($expectedResponse['instructions'], $result['instructions']);
    }

    public function testUpdateAssistantWithMissingId(): void
    {
        $assistantId = 'asst_abc123';
        $name = 'Update Assistant Name';
        $instructions = 'Update instruction';

        $expectedResponse = [
            'id' => null,
            'name' => $name,
            'instruction' => $instructions
        ];

        $response = new MockResponse(
            (string) json_encode($expectedResponse),
            ['http_code' => 400]
        );

        $client = new MockHttpClient(
            $response,
            "https://api.openia.com/v1/assistants/{$assistantId}"
        );


        $assistantApi = new AssistantApi($client);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('OpenAI API Error');


        $assistantApi->updateAssistant($assistantId, $name, $instructions);
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
