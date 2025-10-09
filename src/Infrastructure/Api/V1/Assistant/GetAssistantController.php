<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\GetAssistant\GetAssistant;
use Chatbot\Application\Service\GetAssistant\GetAssistantRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Api\V1\AbstractController;
use Chatbot\Infrastructure\Persistence\Assistant\AssistantRepositoryDoctrine;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetAssistantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $client
    ) {
    }

    #[Route('api/v1/assistant/{ast_id}', 'getAssistant', methods: ['GET'])]
    public function getAssistant(string $ast_id): Response
    {
        try {
            $getAssistantRequest = $this->buildGetAssistantRequest($ast_id);

            $service = new GetAssistant(
                new AssistantRepositoryDoctrine($this->entityManager),
                new AssistantApi($this->client)
            );
            $service->execute($getAssistantRequest);

            $response = $service->getResponse();
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
