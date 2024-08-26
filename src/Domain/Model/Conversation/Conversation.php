<?php

namespace Chatbot\Domain\Model\Conversation;

use Chatbot\Domain\Event\ConversationCreated;
use Chatbot\Domain\Event\PairAdded;
use Chatbot\Domain\EventFacade\EventFacade;
use Chatbot\Domain\Model\Context\ContextId;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Safe\DateTimeImmutable as SafeDateTimeImmutable;

class Conversation
{
    
    private Collection $pairs;
    public function __construct(
      
        private ContextId $context,
        private ConversationId $conversationId = new ConversationId(),
        private readonly DateTimeImmutable $createdAt = new SafeDateTimeImmutable(),
    ) {
        $this->pairs = new ArrayCollection;
        (new EventFacade())->dispatch(new ConversationCreated($this->conversationId->__toString()));
    }

    //public function getTotalToken(): int
    //{
    //    return $this->pairs->TotalToken();
    //}

    public function getConversationId(): ConversationId
    {
        return $this->conversationId;
    }

    public function getPair(int $number): Pair
    {
        return $this->pairs->get($number);
    }

    public function getNbPair(): int
    {
        return $this->pairs->count();
    }

    public function addPair(Prompt $prompt, Answer $message): void
    {
        $this->pairs->add(new Pair($prompt, $message));
        (new EventFacade())->dispatch(new PairAdded($this->conversationId->__toString()));
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getContext(): ContextId
    {
        return $this->context;
    }

    public function clearPair(): void
    {
        $this->pairs->clear;
    }
}
