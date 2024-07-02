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
        $this->repository = new ConversationRepositoryDoctrine($this->getEntityManager());
    }
}
