<?php

namespace Chatbot\Domain\Model\Conversation;

use Chatbot\Domain\Model\Context\ContextId;
use SebastianBergmann\Type\FalseType;

interface ConversationRepositoryInterface
{
    public function add(Conversation $conversation): void;
    public function findById(ConversationId $conversationId): Conversation;
    public function findByContextId(ContextId $contextId): Conversation|false;
}
