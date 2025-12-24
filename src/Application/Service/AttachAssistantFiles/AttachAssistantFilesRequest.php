<?php

namespace Chatbot\Application\Service\AttachAssistantFiles;

use Chatbot\Domain\Model\Assistant\AssistantId;

class AttachAssistantFilesRequest
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
