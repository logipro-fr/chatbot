<?php

namespace Chatbot\Application\Service\UpdateAssistant;

use Chatbot\Domain\Model\Assistant\AssistantId;

class UpdateAssistantRequest
{
    public function __construct(
        public AssistantId $assistantId,
        public ?string $newName,
        public ?string $newInstructions
    ) {
    }
}