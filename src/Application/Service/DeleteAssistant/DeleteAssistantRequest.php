<?php

namespace Chatbot\Application\Service\DeleteAssistant;

use Chatbot\Domain\Model\Assistant\AssistantId;

class DeleteAssistantRequest
{
    public function __construct(
        public AssistantId $assistantId
    ) {
    }
}
