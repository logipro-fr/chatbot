<?php

namespace Chatbot\Application\Service\DetachAssistantFiles;

use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\File\FileId;

class DetachAssistantFilesRequest
{
    /**
     * @param AssistantId $assistant_id
     * @param FileId $file_id
     */
    public function __construct(
        public AssistantId $assistant_id,
        public FileId $file_id
    ) {
    }
}
