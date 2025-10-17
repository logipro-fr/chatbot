<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\DeleteAssistant\DeleteAssistant;
use Chatbot\Application\Service\DeleteAssistant\DeleteAssistantRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Api\V1\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteAssistantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DeleteAssistant $deleteAssistantService
    ) {
    }

    #[Route('api/v1/assistant/{ast_id}', 'deleteAssistant', methods: ['DELETE'])]
    public function deleteAssistant(string $ast_id): Response
    {
        try {
            $deleteAssistantRequest = $this->buildDeleteAssistantRequest($ast_id);

            $this->deleteAssistantService->execute($deleteAssistantRequest);
            $this->entityManager->flush();

            $response = $this->deleteAssistantService->getResponse();
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
