<?php

namespace Chatbot\Application\Service\GetAssistant;

use Chatbot\Domain\Model\Assistant\AssistantId;

class GetAssistantRequest
{
    public function __construct(
        private AssistantId $assistantId
    ) {
    }

    public function getAssistantId(): AssistantId
    {
        return $this->assistantId;
    }
}
