<?php

namespace Chatbot\Application\Service\DeleteAssistantFiles;

use Chatbot\Domain\Model\Assistant\AssistantId;

class DeleteAssistantFilesResponse
{
    /**
     * @param AssistantId $assistant_id
     * @param array<string> $fileIds
     */
    public function __construct(
        public AssistantId $assistant_id,
        public array $fileIds
    ) {
    }
}
