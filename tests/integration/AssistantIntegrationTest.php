<?php

namespace Chatbot\Tests\Integration;

use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContext;
use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContextRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Infrastructure\Persistence\Assistant\AssistantRepositoryDoctrine;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryDoctrine;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AssistantIntegrationTest extends TestCase
{
    public function test_should_create_assistant_from_context_with_doctrine(): void
    {
        // Given
        $context = new Context(new ContextMessage("Tu es un assistant de test"));
        $contextId = $context->getContextId();

        $request = new CreateAssistantFromContextRequest($contextId, ["file_123"]);

        // Mock HttpClient
        $httpClient = $this->createMock(HttpClientInterface::class);

        // Mock AssistantApi
        $assistantApi = $this->createMock(AssistantApi::class);
        $assistantApi->expects($this->once())
            ->method('createAssistant')
            ->with(
                "Assistant basé sur le contexte",
                "Tu es un assistant de test",
                ["file_123"]
            )
            ->willReturn('asst_test123');

        // Mock EntityManager
        $entityManager = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);

        $contextRepository = new ContextRepositoryDoctrine($entityManager);
        $assistantRepository = new AssistantRepositoryDoctrine($entityManager);

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
        $this->assertEquals('asst_test123', $response->openAiAssistantId);
        $this->assertInstanceOf(AssistantId::class, $response->assistantId);
    }
}
