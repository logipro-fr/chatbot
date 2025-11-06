<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Conversation ;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Tests\Infrastructure\Persistence\Conversation\FakeConversationRepositoryDoctrine;
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

    public function testFindByContextIdReturnsConversationWhenFound(): void
    {
        $contextId = new ContextId("test-context-id");
        $conversationId = new ConversationId("test-conversation-id");

        $conversation = new Conversation($contextId, $conversationId);
        $this->repository->add($conversation);
        $this->getEntityManager()->flush();

        $result = $this->repository->findByContextId($contextId);

        $this->assertInstanceOf(Conversation::class, $result);
        $this->assertEquals($conversationId, $result->getConversationId());
        $this->assertTrue($result->getContext()->equals($contextId));
    }

    public function testFindByContextIdReturnsFalseWhenNotFound(): void
    {
        $contextId = new ContextId("non-existent-context");

        $result = $this->repository->findByContextId($contextId);

        $this->assertFalse($result);
    }

    public function testFindByContextIdReturnsCorrectConversationWhenMultipleExist(): void
    {
        $contextId1 = new ContextId("context-1");
        $contextId2 = new ContextId("context-2");

        $conversation1 = new Conversation($contextId1, new ConversationId("conv-1"));
        $conversation2 = new Conversation($contextId2, new ConversationId("conv-2"));
        $conversation3 = new Conversation($contextId1, new ConversationId("conv-3"));

        $this->repository->add($conversation1);
        $this->repository->add($conversation2);
        $this->repository->add($conversation3);
        $this->getEntityManager()->flush();

        $result = $this->repository->findByContextId($contextId1);

        $this->assertInstanceOf(Conversation::class, $result);
        $this->assertTrue($result->getContext()->equals($contextId1));
        $this->assertContains($result->getConversationId()->__toString(), ["conv-1", "conv-3"]);
    }
}
