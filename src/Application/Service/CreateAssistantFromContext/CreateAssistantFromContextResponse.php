<?php

namespace Chatbot\Application\Service\CreateAssistantFromContext;

use Chatbot\Domain\Model\Assistant\AssistantId;

class CreateAssistantFromContextResponse
{
    public function __construct(
        public AssistantId $assistantId,
        public string $externalAssistantId
    ) {
    }
}
