<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\DeleteAssistant\DeleteAssistant;
use Chatbot\Application\Service\DeleteAssistant\DeleteAssistantRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use Chatbot\Infrastructure\Api\V1\AbstractController;
use Chatbot\Infrastructure\Persistence\Assistant\AssistantRepositoryDoctrine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeleteAssistantController extends AbstractController
{
    public function __construct(
        private AssistantApi $assistantApi,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('api/v1/assistant/{ast_id}', 'deleteAssistant', methods: ['DELETE'])]
    public function deleteAssistant(string $ast_id): Response
    {
        try {
            $deleteAssistantRequest = $this->buildDeleteAssistantRequest($ast_id);

            $service = new DeleteAssistant(
                new AssistantRepositoryDoctrine($this->entityManager),
                $this->assistantApi
            );
            $service->execute($deleteAssistantRequest);
            $this->entityManager->flush();

            $response = $service->getResponse();
            return $this->writeSuccessfulResponse($response);
        } catch (\Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
    }

    private function buildDeleteAssistantRequest(string $assistantId): DeleteAssistantRequest
    {
        if (empty($assistantId)) {
            throw new \InvalidArgumentException("L'ID de l'assistant est requis");
        }

        return new DeleteAssistantRequest(new AssistantId($assistantId));
    }
}
