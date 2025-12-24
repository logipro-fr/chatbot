<?php

namespace Chatbot\Application\Service\DetachAssistantFiles;

use Chatbot\Domain\Model\Assistant\AssistantId;

class DetachAssistantFilesResponse
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
