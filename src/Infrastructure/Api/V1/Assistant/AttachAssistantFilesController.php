<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\AttachAssistantFiles\AttachAssistantFiles;
use Chatbot\Application\Service\AttachAssistantFiles\AttachAssistantFilesRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Api\V1\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function Safe\json_decode;

class AttachAssistantFilesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AttachAssistantFiles $attachAssistantFilesService
    ) {
    }

    #[Route('api/v1/assistant/{ast_id}/attach/files', 'attachAssistantFiles', methods: ['PUT'])]
    public function attachAssistantFiles(Request $request, string $ast_id): Response
    {
        try {
            $attachFilesRequest = $this->buildAttachFilesRequest($request, $ast_id);

            $this->attachAssistantFilesService->execute($attachFilesRequest);
            $this->entityManager->flush();

            $response = $this->attachAssistantFilesService->getResponse();
            return $this->writeSuccessfulResponse($response);
        } catch (\Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
    }

    private function buildAttachFilesRequest(Request $request, string $assistantId): AttachAssistantFilesRequest
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

        return new AttachAssistantFilesRequest(new AssistantId($assistantId), $fileIds);
    }
}
