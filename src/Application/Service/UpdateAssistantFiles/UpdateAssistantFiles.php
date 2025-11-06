<?php

namespace Chatbot\Application\Service\UpdateAssistantFiles;

use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;

class UpdateAssistantFiles
{
    private UpdateAssistantFilesResponse $response;

    public function __construct(
        private AssistantRepositoryInterface $assistantRepository
    ) {
    }

    public function execute(UpdateAssistantFilesRequest $request): void
    {
        $assistant = $this->assistantRepository->findById($request->assistantId);
        if ($assistant === null) {
            throw new \InvalidArgumentException("Assistant not found: " . $request->assistantId->getId());
        }

        $currentFileIds = $assistant->getFileIds();

        foreach ($currentFileIds as $fileId) {
            $assistant->removeFileId($fileId);
        }

        foreach ($request->fileIds as $fileId) {
            $assistant->addFileId($fileId);
        }

        $this->assistantRepository->add($assistant);

        $this->response = new UpdateAssistantFilesResponse(
            $assistant->getAssistantId(),
            $assistant->getFileIds()
        );
    }

    public function getResponse(): UpdateAssistantFilesResponse
    {
        return $this->response;
    }
}
