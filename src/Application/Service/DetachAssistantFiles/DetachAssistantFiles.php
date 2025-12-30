<?php

namespace Chatbot\Application\Service\DetachAssistantFiles;

use Chatbot\Application\Service\ChatGPT\AssistantApi;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;

class DetachAssistantFiles
{
    private DetachAssistantFilesResponse $response;

    public function __construct(
        private AssistantRepositoryInterface $assistantRepository,
        private AssistantApi $assistantApi
    ) {
    }

    public function execute(DetachAssistantFilesRequest $request): void
    {
        $assistant = $this->assistantRepository->findById($request->assistant_id);
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

        $this->assistantRepository->add($assistant);


        $this->response = new DetachAssistantFilesResponse(
            $assistant->getAssistantId(),
            $assistant->getFileIds()
        );
    }

    public function getResponse(): DetachAssistantFilesResponse
    {
        return $this->response;
    }
}
