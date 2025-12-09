<?php

namespace Chatbot\Application\Service\UpdateAssistantFiles;

use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Application\Service\ChatGPT\AssistantApi;
use Chatbot\Domain\Model\Assistant\Assistant;

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

        $vectorId = $assistant->getVectorId();

        $currentFileIds = $assistant->getFileIds();

        // On ajoute seulement les nouveaux file_ids
        foreach ($request->fileIds as $fileId) {
            if (!in_array($fileId, $currentFileIds, true)) {
                $assistant->addFileId($fileId);
            }
        }
        $allFileIds = $assistant->getFileIds();

        // Mettre à jour l'assistant dans OpenAI avec le nouveau vector store
        $this->assistantApi->updateAssistantFile(
            $assistant->getExternalAssistantId(),
            $assistant,
            $allFileIds,
            $vectorId
        );

        $this->assistantRepository->add($assistant);

        $this->response = new UpdateAssistantFilesResponse(
            $assistant->getAssistantId(),
            $allFileIds,
            $vectorId
        );
    }

    public function getResponse(): UpdateAssistantFilesResponse
    {
        return $this->response;
    }
}
