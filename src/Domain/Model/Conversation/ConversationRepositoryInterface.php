<?php

namespace Chatbot\Domain\Model\Conversation;

interface ConversationRepositoryInterface
{
    public function add(Conversation $conversation): void;
    public function findById(ConversationId $conversationId):  Conversation|false;
}
