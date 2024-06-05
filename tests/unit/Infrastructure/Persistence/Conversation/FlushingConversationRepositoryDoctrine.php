<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Conversation;

use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;

class FlushingConversationRepositoryDoctrine extends ConversationRepositoryDoctrine
{
    protected function addMapToRepository(Conversation $conversation): void
    {
        parent::addMapToRepository($conversation);
        parent::flush();
    }
}