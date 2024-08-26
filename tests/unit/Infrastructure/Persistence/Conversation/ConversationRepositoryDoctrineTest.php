<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Conversation ;

use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;

class ConversationRepositoryDoctrineTest extends ConversationRepositoryTestBase
{
    use DoctrineRepositoryTesterTrait;

    protected function initialize(): void
    {
        $this->initDoctrineTester();
        $this->clearTables(["conversations_pairs", "conversations", "pairs"]);
        
        $this->repository = new FakeConversationRepositoryDoctrine($this->getEntityManager());
    }
}
