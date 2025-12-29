<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\DetachAssistantFiles\DetachAssistantFiles;
use Chatbot\Application\Service\DetachAssistantFiles\DetachAssistantFilesRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\File\FileId;
use Chatbot\Infrastructure\Api\V1\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use SebastianBergmann\CodeUnit\FunctionUnit;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class DetachAssistantFileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DetachAssistantFiles $detachAssistantFileService
    ) {
    }

    #[Route('/api/v1/assistant/{ast_id}/detach/file/{file_id}', 'detachAssistantFile', methods:['DELETE'])]
    public function detachAssistantFile(string $ast_id, string $file_id): Response
    {
        try {
            $detachAssistantFilesRequest = $this->buildDetachAssistantRequest($ast_id, $file_id);

            $this->detachAssistantFileService->execute($detachAssistantFilesRequest);
            $this->entityManager->flush();

            $response = $this->detachAssistantFileService->getResponse();
            return $this->writeSuccessfulResponse($response);
        } catch (\Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
    }

    public function buildDetachAssistantRequest(string $assistantId, string $fileId): DetachAssistantFilesRequest
    {
        if (empty($assistantId)) {
            throw new \InvalidArgumentException("L'ID de l'assistant est requis");
        }

        return new DetachAssistantFilesRequest(new AssistantId($assistantId), new FileId($fileId));
    }
}
