<?php

namespace Chatbot\Infrastructure\Persistence\Conversation;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Infrastructure\Exception\ContextAssociatedConversationException;
use Chatbot\Infrastructure\Exception\ConversationNotFoundException;

class ConversationRepositoryInMemory implements ConversationRepositoryInterface
{
     /**
     * @var array<Conversation>
     */
    private array $conversations;

    public function add(Conversation $conversation): void
    {
        $this->conversations[$conversation->getConversationId()->__toString()] = $conversation;
    }

    public function findById(ConversationId $id): Conversation
    {
        if (!isset($this->conversations[$id->__toString()])) {
            throw new ConversationNotFoundException(sprintf("Conversation '%s' not found", $id));
        }
        return $this->conversations[$id->__toString()];
    }

    public function findByContextId(ContextId $contextId): Conversation|false
    {
        foreach ($this->conversations as $conversation) {
            if ($conversation->getContext()->equals($contextId)) {
                return $conversation;
            }
        }
        return false;
    }
}
