<?php

namespace Chatbot\Application\Service\UpdateAssistantFiles;

use Chatbot\Domain\Model\Assistant\AssistantId;

class UpdateAssistantFilesResponse
{
    /**
     * @param AssistantId $assistantId
     * @param array<string> $fileIds
     */
    public function __construct(
        public AssistantId $assistantId,
        public array $fileIds
    ) {
    }
}
