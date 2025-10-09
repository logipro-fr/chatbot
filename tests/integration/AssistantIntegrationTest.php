<?php

namespace Chatbot\Tests\Integration;

use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContext;
use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContextRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Infrastructure\Persistence\Assistant\AssistantRepositoryDoctrine;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryDoctrine;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AssistantIntegrationTest extends TestCase
{
    public function testShouldCreateAssistantFromContextWithDoctrine(): void
    {
        $context = new Context(new ContextMessage("Tu es un assistant de test"));
        $contextId = $context->getContextId();

        $request = new CreateAssistantFromContextRequest($contextId, ["fil_123"]);


        $assistantApi = $this->createMock(AssistantApi::class);
        $assistantApi->expects($this->once())
            ->method('createAssistant')
            ->with(
                "Assistant basé sur le contexte",
                "Tu es un assistant de test",
                ["fil_123"]
            )
            ->willReturn('asst_test123');

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $contextRepository = new ContextRepositoryDoctrine($entityManager);
        $assistantRepository = new AssistantRepositoryDoctrine($entityManager);

        $service = new CreateAssistantFromContext(
            $assistantRepository,
            $contextRepository,
            $assistantApi
        );

        $service->execute($request);

        $response = $service->getResponse();
        $this->assertEquals('asst_test123', $response->externalAssistantId);
        $this->assertInstanceOf(AssistantId::class, $response->assistantId);
    }
}
