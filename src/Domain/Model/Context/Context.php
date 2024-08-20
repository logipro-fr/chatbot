<?php

namespace Chatbot\Domain\Model\Context;

use Chatbot\Domain\Event\ContextCreated;
use Chatbot\Domain\EventFacade\EventFacade;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use DateTimeImmutable;
use Safe\DateTimeImmutable as SafeDateTimeImmutable;

class Context
{
    public function __construct(
        private ContextMessage $contextmessage,
        private ContextId $id = new ContextId(),
        private readonly DateTimeImmutable $createdAt = new SafeDateTimeImmutable()
    ) {
        (new EventFacade())->dispatch(new ContextCreated($this->id->__toString(), $this->contextmessage->getMessage()));
    }

    public function getContext(): ContextMessage
    {
        return $this->contextmessage;
    }

    public function getId(): ContextId
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function editMessage(ContextMessage $context): void
    {
        $this->contextmessage = $context;
        (new EventFacade())->dispatch(new ContextCreated($this->id->__toString(), $this->contextmessage->getMessage()));
    }
}
