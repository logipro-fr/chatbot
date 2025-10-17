<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\GetAssistant\GetAssistant;
use Chatbot\Application\Service\GetAssistant\GetAssistantRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Api\V1\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GetAssistantController extends AbstractController
{
    public function __construct(
        private GetAssistant $getAssistantService
    ) {
    }

    #[Route('api/v1/assistant/{ast_id}', 'getAssistant', methods: ['GET'])]
    public function getAssistant(string $ast_id): Response
    {
        try {
            $getAssistantRequest = $this->buildGetAssistantRequest($ast_id);

            $this->getAssistantService->execute($getAssistantRequest);

            $response = $this->getAssistantService->getResponse();
            if ($response === null) {
                throw new \RuntimeException("Service response is null");
            }
            $responseData = $response->toArray();
            /** @var array<string, array<string, bool|int|string>|bool|int|string> $responseData */
            return $this->writeSuccessfulResponse($responseData);
        } catch (\Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
    }

    private function buildGetAssistantRequest(string $assistantId): GetAssistantRequest
    {
        if (empty($assistantId)) {
            throw new \InvalidArgumentException("L'ID de l'assistant est requis");
        }

        return new GetAssistantRequest(new AssistantId($assistantId));
    }
}
