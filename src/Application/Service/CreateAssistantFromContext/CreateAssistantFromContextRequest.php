<?php

namespace Chatbot\Application\Service\CreateAssistantFromContext;

use Chatbot\Domain\Model\Context\ContextId;

class CreateAssistantFromContextRequest
{
    /**
     * @param ContextId $contextId
     * @param array<string> $fileIds
     */
    public function __construct(
        public ContextId $contextId,
        public array $fileIds = []
    ) {
    }
}
