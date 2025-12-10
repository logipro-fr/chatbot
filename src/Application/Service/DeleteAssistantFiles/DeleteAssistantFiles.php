<?php

namespace Chatbot\Application\Service\DeleteAssistantFiles;

use Chatbot\Application\Service\ChatGPT\AssistantApi;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;

class DeleteAssistantFiles
{
    private DeleteAssistantFilesResponse $response;

    public function __construct(
        private AssistantRepositoryInterface $assistant_repository,
        private AssistantApi $assistantApi
    ) {
    }

    public function execute(DeleteAssistantFilesRequest $request): void
    {
        $assistant = $this->assistant_repository->findById($request->assistant_id);
        if ($assistant === null) {
            throw new \InvalidArgumentException("Assistant not found: " . $request->assistant_id->getId());
        }

        $vectorId = $assistant->getVectorId();

        $currentFileIds = $assistant->getFileIds();
        $fileIdToRemove = $request->file_id;

        $this->assistantApi->deleteVectorStoreFile((string) $vectorId, (string) $fileIdToRemove);

        if (in_array((string)$fileIdToRemove, $currentFileIds, true)) {
            $assistant->removeFileId($fileIdToRemove);
        }

        $this->assistant_repository->add($assistant);


        $this->response = new DeleteAssistantFilesResponse(
            $assistant->getAssistantId(),
            $assistant->getFileIds()
        );
    }

    public function getResponse(): DeleteAssistantFilesResponse
    {
        return $this->response;
    }
}
