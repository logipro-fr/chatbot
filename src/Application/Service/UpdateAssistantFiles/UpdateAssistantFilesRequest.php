<?php

namespace Chatbot\Application\Service\UpdateAssistantFiles;

use Chatbot\Domain\Model\Assistant\AssistantId;

class UpdateAssistantFilesRequest
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
