<?php

namespace Chatbot\Domain\Model\Context;

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
}
