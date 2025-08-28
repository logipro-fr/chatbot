<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\DeleteAssistant\DeleteAssistant;
use Chatbot\Application\Service\DeleteAssistant\DeleteAssistantRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Api\V1\AbstractController;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use Chatbot\Infrastructure\Persistence\Assistant\AssistantRepositoryDoctrine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Safe\json_decode;

class DeleteAssistantController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('api/v1/assistant/{assistant_id}', 'deleteAssistant', methods: ['DELETE'])]
    public function deleteAssistant(Request $request, string $assistant_id): Response
    {
        try {
            $deleteAssistantRequest = $this->buildDeleteAssistantRequest($assistant_id);

            $service = new DeleteAssistant(
                new AssistantRepositoryDoctrine($this->entityManager),
                new AssistantApi($this->client)
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
