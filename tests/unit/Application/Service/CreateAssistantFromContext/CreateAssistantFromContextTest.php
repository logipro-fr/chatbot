<?php

namespace Chatbot\Tests\Unit\Application\Service\CreateAssistantFromContext;

use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContext;
use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContextRequest;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use PHPUnit\Framework\TestCase;

class CreateAssistantFromContextTest extends TestCase
{
    public function test_should_create_assistant_from_context(): void
    {
        // Given
        $contextId = new ContextId();
        $context = new Context(new ContextMessage("Tu es un assistant spécialisé dans la documentation technique"));

        $request = new CreateAssistantFromContextRequest($contextId, ["file_123", "file_456"]);

        $contextRepository = $this->createMock(ContextRepositoryInterface::class);
        $assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $assistantApi = $this->createMock(AssistantApi::class);

        $contextRepository->expects($this->once())
            ->method('findById')
            ->with($contextId)
            ->willReturn($context);

        $assistantApi->expects($this->once())
            ->method('createAssistant')
            ->with(
                "Assistant basé sur le contexte",
                "Tu es un assistant spécialisé dans la documentation technique",
                ["file_123", "file_456"]
            )
            ->willReturn('asst_abc123');

        $assistantRepository->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(Assistant::class));

        $service = new CreateAssistantFromContext(
            $assistantRepository,
            $contextRepository,
            $assistantApi
        );

        // When
        $service->execute($request);

        // Then
        $response = $service->getResponse();
        $this->assertNotNull($response);
        $this->assertEquals('asst_abc123', $response->openAiAssistantId);
        $this->assertInstanceOf(AssistantId::class, $response->assistantId);
    }

    public function test_should_create_assistant_without_files(): void
    {
        // Given
        $contextId = new ContextId();
        $context = new Context(new ContextMessage("Tu es un assistant de support"));

        $request = new CreateAssistantFromContextRequest($contextId, []);

        $contextRepository = $this->createMock(ContextRepositoryInterface::class);
        $assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $assistantApi = $this->createMock(AssistantApi::class);

        $contextRepository->expects($this->once())
            ->method('findById')
            ->with($contextId)
            ->willReturn($context);

        $assistantApi->expects($this->once())
            ->method('createAssistant')
            ->with("Assistant basé sur le contexte", "Tu es un assistant de support", [])
            ->willReturn('asst_support123');

        $service = new CreateAssistantFromContext(
            $assistantRepository,
            $contextRepository,
            $assistantApi
        );

        // When
        $service->execute($request);

        // Then
        $response = $service->getResponse();
        $this->assertEquals('asst_support123', $response->openAiAssistantId);
    }

    public function test_should_throw_exception_when_context_not_found(): void
    {
        // Given
        $contextId = new ContextId();
        $request = new CreateAssistantFromContextRequest($contextId, []);

        $contextRepository = $this->createMock(ContextRepositoryInterface::class);
        $assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $assistantApi = $this->createMock(AssistantApi::class);

        $contextRepository->expects($this->once())
            ->method('findById')
            ->with($contextId)
            ->willThrowException(new \InvalidArgumentException("Contexte non trouvé"));

        $service = new CreateAssistantFromContext(
            $assistantRepository,
            $contextRepository,
            $assistantApi
        );

        // When & Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Contexte non trouvé");

        $service->execute($request);
    }
}
