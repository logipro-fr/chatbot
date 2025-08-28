<?php

namespace Chatbot\Application\Service\DeleteAssistant;

use Chatbot\Domain\Model\Assistant\AssistantId;

class DeleteAssistantResponse
{
    public function __construct(
        public AssistantId $assistantId,
        public string $openAiAssistantId
    ) {
    }
}
