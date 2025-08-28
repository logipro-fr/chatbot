<?php

namespace Chatbot\Domain\Event;

use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Thread\ThreadId;
use Phariscope\Event\Psr14\Event;

class ThreadCreated extends Event
{
    public function __construct(
        private ThreadId $threadId,
        private AssistantId $assistantId
    ) {
        parent::__construct();
    }

    public function getThreadId(): ThreadId
    {
        return $this->threadId;
    }

    public function getAssistantId(): AssistantId
    {
        return $this->assistantId;
    }
}
