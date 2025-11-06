<?php

namespace Chatbot\Domain\Event;

use Chatbot\Domain\Model\Assistant\AssistantId;
use Phariscope\Event\Psr14\Event;

class AssistantCreated extends Event
{
    public function __construct(
        private AssistantId $assistantId,
        private string $name
    ) {
        parent::__construct();
    }

    public function getAssistantId(): AssistantId
    {
        return $this->assistantId;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
