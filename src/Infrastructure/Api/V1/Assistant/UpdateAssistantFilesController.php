<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFiles;
use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFilesRequest;
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

class UpdateAssistantFilesController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('api/v1/assistant/{assistant_id}/files', 'updateAssistantFiles', methods: ['PUT'])]
    public function updateAssistantFiles(Request $request, string $assistant_id): Response
    {
        try {
            $updateFilesRequest = $this->buildUpdateFilesRequest($request, $assistant_id);

            $service = new UpdateAssistantFiles(
                new AssistantRepositoryDoctrine($this->entityManager),
                new AssistantApi($this->client)
            );
            $service->execute($updateFilesRequest);
            $this->entityManager->flush();

            $response = $service->getResponse();
            return $this->writeSuccessfulResponse($response);
        } catch (\Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
    }

    private function buildUpdateFilesRequest(Request $request, string $assistantId): UpdateAssistantFilesRequest
    {
        $content = $request->getContent();
        /** @var array<string, mixed> $data */
        $data = json_decode($content, true);

        $fileIds = $data['file_ids'] ?? [];

        if (empty($assistantId)) {
            throw new \InvalidArgumentException("L'ID de l'assistant est requis");
        }

        return new UpdateAssistantFilesRequest(new AssistantId($assistantId), $fileIds);
    }
}
