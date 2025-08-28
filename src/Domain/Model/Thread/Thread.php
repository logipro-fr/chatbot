<?php

namespace Chatbot\Domain\Model\Thread;

use Chatbot\Domain\Event\ThreadCreated;
use Chatbot\Domain\EventFacade\EventFacade;
use Chatbot\Domain\Model\Assistant\AssistantId;
use DateTimeImmutable;
use Safe\DateTimeImmutable as SafeDateTimeImmutable;

class Thread
{
    public function __construct(
        private ThreadId $threadId,
        private AssistantId $assistantId,
        private string $openAiThreadId,
        private readonly DateTimeImmutable $createdAt = new SafeDateTimeImmutable()
    ) {
        (new EventFacade())->dispatch(new ThreadCreated($this->threadId, $this->assistantId));
    }

    public function getThreadId(): ThreadId
    {
        return $this->threadId;
    }

    public function getAssistantId(): AssistantId
    {
        return $this->assistantId;
    }

    public function getOpenAiThreadId(): string
    {
        return $this->openAiThreadId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
