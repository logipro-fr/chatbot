<?php

namespace Chatbot\Domain\Model\Conversation;

use Chatbot\Domain\Event\ConversationCreated;
use Chatbot\Domain\Event\PairAdded;
use Chatbot\Domain\EventFacade\EventFacade;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Exceptions\LastPairDoesntExistException;
use Chatbot\Domain\Model\Conversation\Exceptions\PairOutOfRangeException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Safe\DateTimeImmutable as SafeDateTimeImmutable;

class Conversation
{
    /** @var Collection<int, Pair> */
    private Collection $pairs;

    public function __construct(
        private ContextId $context,
        private ConversationId $conversationId = new ConversationId(),
        private readonly DateTimeImmutable $createdAt = new SafeDateTimeImmutable(),
    ) {
        $this->pairs = new ArrayCollection();
        (new EventFacade())->dispatch(new ConversationCreated($this->conversationId));
    }

    public function getConversationId(): ConversationId
    {
        return $this->conversationId;
    }

    public function getPair(int $number): Pair
    {
        $pair = $this->pairs->get($number);
        if (null === $pair) {
            throw new PairOutOfRangeException(sprintf(
                "Index '%d' out of range, pair cannot be found",
                $number
            ));
        }
        return $pair;
    }

    public function getLastPair(): Pair
    {
        $pair = $this->pairs->last();
        if (false === $pair) {
            throw new LastPairDoesntExistException(
                "The last pair cannot be found"
            );
        }
        return $pair;
    }

    public function countPair(): int
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
}
