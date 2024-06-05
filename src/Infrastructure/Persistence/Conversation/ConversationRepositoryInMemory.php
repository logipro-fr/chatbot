<?php

namespace Chatbot\Infrastructure\Persistence\Conversation;

use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;

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
}
