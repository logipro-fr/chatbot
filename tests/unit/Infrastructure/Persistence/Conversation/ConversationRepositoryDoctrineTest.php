<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Conversation ;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Prompt;
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

    public function testPairsAreCorrectlyPersisted(): void
    {
        $id = new ConversationId("unId");
        $context = new ContextId("Contextid");

        $conversation = new Conversation($context, $id);

        $this->repository->add($conversation);

        $foundConversation = $this->repository->findById($id);

        $foundConversation->addPair(new Prompt("prompt 1"), new Answer("answer 1", 0));
        $foundConversation->addPair(new Prompt("prompt 2"), new Answer("answer 2", 5));

        $this->getEntityManager()->flush();
        $this->getEntityManager()->detach($foundConversation);

        $sut = $this->repository->findById($id);

        $this->assertEquals(2, $sut->countPair());
    }
}
