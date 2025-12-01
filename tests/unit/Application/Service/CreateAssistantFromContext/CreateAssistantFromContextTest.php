<?php

namespace Chatbot\Tests\Unit\Application\Service\CreateAssistantFromContext;

use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContext;
use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContextRequest;
use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContextResponse;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Application\Service\ChatGPT\AssistantApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CreateAssistantFromContextTest extends TestCase
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

    public function testExecuteCreatesAssistantWithFiles(): void
    {
        $contextId = new ContextId('context-123');
        $fileIds = ['file-123', 'file-456'];
        $expectedOpenAiId = 'asst-abc123';

        $context = $this->createMock(Context::class);
        $contextMessage = $this->createMock(\Chatbot\Domain\Model\Context\ContextMessage::class);
        $contextMessage->method('getMessage')->willReturn('Test context message');
        $context->method('getContext')->willReturn($contextMessage);

        $contextRepository = $this->createMock(ContextRepositoryInterface::class);
        $contextRepository->method('findById')->willReturn($context);

        $assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $assistantRepository->expects($this->once())->method('add');

        $responses = [
            new MockResponse((string) json_encode(['id' => 'vs-xyz789']), ['http_code' => 200]),
            new MockResponse((string) json_encode(['id' => $expectedOpenAiId]), ['http_code' => 200])
        ];

        $client = new MockHttpClient($responses);
        $assistantApi = new AssistantApi($client);

        $createAssistant = new CreateAssistantFromContext(
            $assistantRepository,
            $contextRepository,
            $assistantApi
        );

        $request = new CreateAssistantFromContextRequest($contextId, $fileIds);
        $createAssistant->execute($request);

        $response = $createAssistant->getResponse();
        $this->assertInstanceOf(CreateAssistantFromContextResponse::class, $response);
    }

    public function testExecuteCreatesAssistantWithoutFiles(): void
    {
        $contextId = new ContextId('context-456');
        $expectedOpenAiId = 'asst-def456';

        $context = $this->createMock(Context::class);
        $contextMessage = $this->createMock(\Chatbot\Domain\Model\Context\ContextMessage::class);
        $contextMessage->method('getMessage')->willReturn('Test context message');
        $context->method('getContext')->willReturn($contextMessage);

        $contextRepository = $this->createMock(ContextRepositoryInterface::class);
        $contextRepository->method('findById')->willReturn($context);

        $assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $assistantRepository->expects($this->once())->method('add');

        $response = new MockResponse((string) json_encode(['id' => $expectedOpenAiId]), ['http_code' => 200]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
        $assistantApi = new AssistantApi($client);

        $createAssistant = new CreateAssistantFromContext(
            $assistantRepository,
            $contextRepository,
            $assistantApi
        );

        $request = new CreateAssistantFromContextRequest($contextId, []);
        $createAssistant->execute($request);

        $response = $createAssistant->getResponse();
        $this->assertInstanceOf(CreateAssistantFromContextResponse::class, $response);
    }

    public function testExecuteThrowsExceptionWhenContextNotFound(): void
    {
        $contextId = new ContextId('context-789');

        $contextRepository = $this->createMock(ContextRepositoryInterface::class);
        $contextRepository->method('findById')
            ->willThrowException(new \Chatbot\Infrastructure\Exception\ContextNotFoundException('Context not found'));

        $this->expectException(\Chatbot\Infrastructure\Exception\ContextNotFoundException::class);

        $createAssistant = new CreateAssistantFromContext(
            $this->createMock(AssistantRepositoryInterface::class),
            $contextRepository,
            new AssistantApi($this->client)
        );

        $request = new CreateAssistantFromContextRequest($contextId, []);
        $createAssistant->execute($request);
    }

    public function testExecutePropagatesAssistantApiException(): void
    {
        $contextId = new ContextId('context-123');
        $context = $this->createMock(Context::class);
        $contextMessage = $this->createMock(\Chatbot\Domain\Model\Context\ContextMessage::class);
        $contextMessage->method('getMessage')->willReturn('Test context message');
        $context->method('getContext')->willReturn($contextMessage);

        $contextRepository = $this->createMock(ContextRepositoryInterface::class);
        $contextRepository->method('findById')->willReturn($context);

        $response = new MockResponse('{"error":{"message":"API Error"}}', ['http_code' => 400]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
        $assistantApi = new AssistantApi($client);

        $this->expectException(\Exception::class);

        $createAssistant = new CreateAssistantFromContext(
            $this->createMock(AssistantRepositoryInterface::class),
            $contextRepository,
            $assistantApi
        );

        $request = new CreateAssistantFromContextRequest($contextId, []);
        $createAssistant->execute($request);
    }

    public function testGetResponseReturnsResponseObject(): void
    {
        $contextId = new ContextId('context-123');
        $context = $this->createMock(Context::class);
        $contextMessage = $this->createMock(\Chatbot\Domain\Model\Context\ContextMessage::class);
        $contextMessage->method('getMessage')->willReturn('Test context message');
        $context->method('getContext')->willReturn($contextMessage);

        $contextRepository = $this->createMock(ContextRepositoryInterface::class);
        $contextRepository->method('findById')->willReturn($context);

        $assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $assistantRepository->method('add');

        $response = new MockResponse((string) json_encode(['id' => 'asst-123']), ['http_code' => 200]);
        $client = new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
        $assistantApi = new AssistantApi($client);

        $createAssistant = new CreateAssistantFromContext(
            $assistantRepository,
            $contextRepository,
            $assistantApi
        );

        $request = new CreateAssistantFromContextRequest($contextId, []);
        $createAssistant->execute($request);

        $response = $createAssistant->getResponse();
        $this->assertInstanceOf(CreateAssistantFromContextResponse::class, $response);
    }

    private function createMockHttpClient(): MockHttpClient
    {
        $response = new MockResponse('{"id":"asst-test"}', ['http_code' => 200]);
        return new MockHttpClient($response, 'https://api.openai.com/v1/assistants');
    }
}
