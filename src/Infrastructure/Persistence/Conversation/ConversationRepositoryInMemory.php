<?php

namespace Chatbot\Infrastructure\Persistence\Conversation;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Infrastructure\Exception\ContextAssociatedConversationException;
use SebastianBergmann\Type\FalseType;

class ConversationRepositoryInMemory implements ConversationRepositoryInterface
{
     /**
     * @var array<Conversation>
     */
    private array $conversations;

    public function add(Conversation $conversation): void
    {
        $this->conversations[$conversation->getId()->__toString()] = $conversation;
    }

    public function findById(ConversationId $id): Conversation
    {

        return $this->conversations[$id->__toString()];
    }

    public function findByContextId(ContextId $contextId): Conversation|false
    {
        foreach ($this->conversations as $conversation) {
            if ($conversation->getContext()->equals($contextId)) {
                throw new ContextAssociatedConversationException(
                    "The context can't be deleted, is associated at " . $conversation->getId() . " conversation"
                );
            }
        }
        return false;
    }
}
