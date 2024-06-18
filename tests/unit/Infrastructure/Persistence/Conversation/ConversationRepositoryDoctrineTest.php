<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Conversation ;

use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;
use Chatbot\Tests\Infrastructure\Persistence\Conversation\FlushingConversationRepositoryDoctrine;
use Doctrine\DBAL\Types\Type;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;

class ConversationRepositoryDoctrineTest extends ConversationRepositoryTestBase
{
    use DoctrineRepositoryTesterTrait;

    protected function initialize(): void
    {
        $this->initDoctrineTester();
        $this->repository = new ConversationRepositoryDoctrine($this->getEntityManager());
    }

    public function testFlush(): void
    {
        $this->initDoctrineTester();
        $repository = new ConversationRepositoryDoctrine($this->getEntityManager());

        $repository->flush();
        $this->assertTrue(true);
    }
}
