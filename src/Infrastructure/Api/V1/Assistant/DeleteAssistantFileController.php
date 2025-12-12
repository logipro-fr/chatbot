<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\DeleteAssistantFiles\DeleteAssistantFiles;
use Chatbot\Application\Service\DeleteAssistantFiles\DeleteAssistantFilesRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\File\FileId;
use Chatbot\Infrastructure\Api\V1\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use SebastianBergmann\CodeUnit\FunctionUnit;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class DeleteAssistantFileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DeleteAssistantFiles $deleteAssistantFileService
    ) {
    }

    #[Route('/api/v1/assistant/{ast_id}/files/{file_id}', 'deleteAssistantFile', methods:['DELETE'])]
    public function deleteAssistantFile(string $ast_id, string $file_id): Response
    {
        try {
            $deleteAssistantFileRequest = $this->buildDeleteAssistantRequest($ast_id, $file_id);

            $this->deleteAssistantFileService->execute($deleteAssistantFileRequest);
            $this->entityManager->flush();

            $response = $this->deleteAssistantFileService->getResponse();
            return $this->writeSuccessfulResponse($response);
        } catch (\Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
    }

    public function buildDeleteAssistantRequest(string $assistantId, string $fileId): DeleteAssistantFilesRequest
    {
        if (empty($assistantId)) {
            throw new \InvalidArgumentException("L'ID de l'assistant est requis");
        }

        return new DeleteAssistantFilesRequest(new AssistantId($assistantId), new FileId($fileId));
    }
}
