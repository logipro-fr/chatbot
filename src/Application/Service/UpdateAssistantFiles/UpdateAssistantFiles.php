<?php

namespace Chatbot\Application\Service\UpdateAssistantFiles;

use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Application\Service\ChatGPT\AssistantApi;

class UpdateAssistantFiles
{
    private UpdateAssistantFilesResponse $response;

    public function __construct(
        private AssistantRepositoryInterface $assistantRepository,
        private AssistantApi $assistantApi
    ) {
    }

    public function execute(UpdateAssistantFilesRequest $request): void
    {
        $assistant = $this->assistantRepository->findById($request->assistantId);
        if ($assistant === null) {
            throw new \InvalidArgumentException("Assistant not found: " . $request->assistantId->getId());
        }

        $currentFileIds = $assistant->getFileIds();

        // On ajoute seulement les nouveaux file_ids
        foreach ($request->fileIds as $fileId) {
            if (!in_array($fileId, $currentFileIds, true)) {
                $assistant->addFileId($fileId);
            }
        }
        $allFileIds = $assistant->getFileIds();

        // Mettre à jour l'assistant dans OpenAI avec le nouveau vector store
        $this->assistantApi->updateAssistantFile($assistant->getExternalAssistantId(), $allFileIds, $assistant->getVectorId());

        $this->assistantRepository->add($assistant);

        $this->response = new UpdateAssistantFilesResponse(
            $assistant->getAssistantId(),
            $allFileIds,
            $assistant->getVectorId());
    }

    public function getResponse(): UpdateAssistantFilesResponse
    {
        return $this->response;
    }
}
