<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Conversation;

use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;

class FakeConversationRepositoryDoctrine extends ConversationRepositoryDoctrine
{
    public function add(Conversation $conversation): void
    {
        parent::add($conversation);
        parent::getEntityManager()->flush();
        parent::getEntityManager()->detach($conversation);
    }
}
