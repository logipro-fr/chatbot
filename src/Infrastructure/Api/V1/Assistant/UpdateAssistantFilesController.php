<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFiles;
use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFilesRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Api\V1\AbstractController;
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
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('api/v1/assistant/{ast_id}', 'updateAssistantFiles', methods: ['PUT'])]
    public function updateAssistantFiles(Request $request, string $ast_id): Response
    {
        try {
            $updateFilesRequest = $this->buildUpdateFilesRequest($request, $ast_id);

            $service = new UpdateAssistantFiles(
                new AssistantRepositoryDoctrine($this->entityManager)
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

        $fileIdsArray = (array) ($data['file_ids'] ?? []);
        /** @var array<string> $fileIds */
        $fileIds = [];
        foreach ($fileIdsArray as $fileId) {
            if (is_string($fileId)) {
                $fileIds[] = $fileId;
            } elseif (is_scalar($fileId)) {
                $fileIds[] = (string) $fileId;
            }
        }

        if (empty($assistantId)) {
            throw new \InvalidArgumentException("L'ID de l'assistant est requis");
        }

        return new UpdateAssistantFilesRequest(new AssistantId($assistantId), $fileIds);
    }
}
