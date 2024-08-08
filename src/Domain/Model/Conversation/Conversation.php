<?php

namespace Chatbot\Domain\Model\Conversation;

use Chatbot\Domain\Event\ConversationCreated;
use Chatbot\Domain\Event\PairAdded;
use Chatbot\Domain\EventFacade\EventFacade;
use Chatbot\Domain\Model\Context\ContextId;
use DateTimeImmutable;
use Safe\DateTimeImmutable as SafeDateTimeImmutable;

class Conversation
{
    public function __construct(
        private PairArray $pairs,
        private ContextId $context,
        private ConversationId $id = new ConversationId(),
        private readonly DateTimeImmutable $createdAt = new SafeDateTimeImmutable(),
    ) {
        (new EventFacade())->dispatch(new ConversationCreated($this->id->__toString()));
    }

    public function getTotalToken(): int
    {
        return $this->pairs->TotalToken();
    }

    public function getId(): ConversationId
    {
        return $this->id;
    }

    public function getPair(int $number): Pair
    {
        return $this->pairs->getPair($number);
    }

    public function getNbPair(): int
    {
        return $this->pairs->getNB();
    }

    public function addPair(Prompt $prompt, Answer $message): void
    {
        $this->pairs->add(new Pair($prompt, $message));
        (new EventFacade())->dispatch(new PairAdded($this->id->__toString()));
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getContext(): ContextId
    {
        return $this->context;
    }

    public function ChangeContext(ContextId $contextId): void
    {
        $this->context = $contextId; 
    }
}
