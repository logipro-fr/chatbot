<?php

namespace Chatbot\Tests\Unit\Application\Service\GetAssistant;

use Chatbot\Application\Service\GetAssistant\GetAssistant;
use Chatbot\Application\Service\GetAssistant\GetAssistantRequest;
use Chatbot\Application\Service\GetAssistant\GetAssistantResponse;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetAssistantTest extends TestCase
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

    public function testExecuteReturnsAssistantWithoutOpenAiDataWhenApiFails(): void
    {
        $assistantId = new AssistantId('assistant-123');
        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn($assistantId);
        $assistant->method('getName')->willReturn('Test Assistant');
        $assistant->method('getInstructions')->willReturn('Test instructions');
        $assistant->method('getExternalAssistantId')->willReturn('asst-123');
        $assistant->method('getFileIds')->willReturn(['file-123']);
        $assistant->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2024-01-01 10:00:00'));

        $assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $assistantRepository->method('findById')->willReturn($assistant);

        $response = new MockResponse('{"error":{"message":"API Error"}}', ['http_code' => 400]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
        $assistantApi = new AssistantApi($client);

        $getAssistant = new GetAssistant($assistantRepository, $assistantApi);

        $request = new GetAssistantRequest($assistantId);
        $getAssistant->execute($request);

        $response = $getAssistant->getResponse();
        $this->assertInstanceOf(GetAssistantResponse::class, $response);
        $this->assertNull($response->getExternalData());
    }

    public function testExecuteReturnsAssistantWithOpenAiData(): void
    {
        $assistantId = new AssistantId('assistant-456');
        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn($assistantId);
        $assistant->method('getName')->willReturn('Test Assistant');
        $assistant->method('getInstructions')->willReturn('Test instructions');
        $assistant->method('getExternalAssistantId')->willReturn('asst-456');
        $assistant->method('getFileIds')->willReturn(['file-456']);
        $assistant->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2024-01-01 10:00:00'));

        $externalData = [
            'id' => 'asst-456',
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

        $assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $assistantRepository->method('findById')->willReturn($assistant);

        $response = new MockResponse((string) json_encode($externalData), ['http_code' => 200]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
        $assistantApi = new AssistantApi($client);

        $getAssistant = new GetAssistant($assistantRepository, $assistantApi);

        $request = new GetAssistantRequest($assistantId);
        $getAssistant->execute($request);

        $response = $getAssistant->getResponse();
        $this->assertInstanceOf(GetAssistantResponse::class, $response);
        $this->assertEquals($externalData, $response->getExternalData());
    }

    public function testExecuteThrowsExceptionWhenAssistantNotFound(): void
    {
        $assistantId = new AssistantId('assistant-999');

        $assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $assistantRepository->method('findById')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Assistant non trouvé');

        $getAssistant = new GetAssistant($assistantRepository, new AssistantApi($this->client));

        $request = new GetAssistantRequest($assistantId);
        $getAssistant->execute($request);
    }

    public function testToArrayIncludesOpenAiDataWhenAvailable(): void
    {
        $assistantId = new AssistantId('assistant-123');
        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn($assistantId);
        $assistant->method('getName')->willReturn('Test Assistant');
        $assistant->method('getInstructions')->willReturn('Test instructions');
        $assistant->method('getExternalAssistantId')->willReturn('asst-123');
        $assistant->method('getFileIds')->willReturn(['file-123']);
        $assistant->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2024-01-01 10:00:00'));

        $externalData = ['id' => 'asst-123', 'model' => 'gpt-4-turbo'];

        $assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $assistantRepository->method('findById')->willReturn($assistant);

        $response = new MockResponse((string) json_encode($externalData), ['http_code' => 200]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
        $assistantApi = new AssistantApi($client);

        $getAssistant = new GetAssistant($assistantRepository, $assistantApi);

        $request = new GetAssistantRequest($assistantId);
        $getAssistant->execute($request);

        $response = $getAssistant->getResponse();
        if ($response === null) {
            $this->fail('Response is null');
        }
        $data = $response->toArray();

        $this->assertArrayHasKey('externalData', $data);
        $this->assertEquals($externalData, $data['externalData']);
        $this->assertEquals('assistant-123', $data['assistantId']);
        $this->assertEquals('Test Assistant', $data['name']);
    }

    public function testToArrayExcludesExternalDataWhenNotAvailable(): void
    {
        $assistantId = new AssistantId('assistant-123');
        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn($assistantId);
        $assistant->method('getName')->willReturn('Test Assistant');
        $assistant->method('getInstructions')->willReturn('Test instructions');
        $assistant->method('getExternalAssistantId')->willReturn('asst-123');
        $assistant->method('getFileIds')->willReturn(['file-123']);
        $assistant->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2024-01-01 10:00:00'));

        $assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $assistantRepository->method('findById')->willReturn($assistant);

        $response = new MockResponse('{"error":{"message":"API Error"}}', ['http_code' => 400]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
        $assistantApi = new AssistantApi($client);

        $getAssistant = new GetAssistant($assistantRepository, $assistantApi);

        $request = new GetAssistantRequest($assistantId);
        $getAssistant->execute($request);

        $response = $getAssistant->getResponse();
        if ($response === null) {
            $this->fail('Response is null');
        }
        $data = $response->toArray();

        $this->assertArrayNotHasKey('externalData', $data);
        $this->assertEquals('assistant-123', $data['assistantId']);
        $this->assertEquals('Test Assistant', $data['name']);
    }

    public function testGetAssistantResponseGetAssistantMethod(): void
    {
        $assistantId = new AssistantId('assistant-123');
        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn($assistantId);
        $assistant->method('getName')->willReturn('Test Assistant');
        $assistant->method('getInstructions')->willReturn('Test instructions');
        $assistant->method('getExternalAssistantId')->willReturn('asst-123');
        $assistant->method('getFileIds')->willReturn(['file-123']);
        $assistant->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2024-01-01 10:00:00'));

        $externalData = ['id' => 'asst-123', 'model' => 'gpt-4-turbo'];

        $response = new GetAssistantResponse($assistant, $externalData);

        $retrievedAssistant = $response->getAssistant();
        $this->assertSame($assistant, $retrievedAssistant);
        $this->assertEquals($assistantId, $retrievedAssistant->getAssistantId());
        $this->assertEquals('Test Assistant', $retrievedAssistant->getName());
    }

    private function createMockHttpClient(): MockHttpClient
    {
        $response = new MockResponse('{"id":"asst-test"}', ['http_code' => 200]);
        return new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
    }
}
