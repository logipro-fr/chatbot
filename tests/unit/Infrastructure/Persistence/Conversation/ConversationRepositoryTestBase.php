<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Conversation ;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\PairArray;
use Chatbot\Infrastructure\Exception\ContextAssociatedConversationException;
use PHPUnit\Framework\TestCase;

abstract class ConversationRepositoryTestBase extends TestCase
{
    protected ConversationRepositoryInterface $repository;
    protected function setUp(): void
    {
        $this->initialize();
    }

    abstract protected function initialize(): void;

    public function testFindById(): void
    {

        $id = new ConversationId("unId");
        $context = new ContextId("Contextid");

        $conversation = new Conversation(new PairArray(), $context, $id,);

        $conversation2 = new Conversation(new PairArray(), $context, new ConversationId("id2"));


        $this->repository->add($conversation);
        $found = $this->repository->findById($id);
        $this->repository->add($conversation2);
        $found2 = $this->repository->findById(new ConversationId("id2"));
        /** @var Conversation */
        $found = $this->repository->findById($id);
        /** @var Conversation */
        $found2 = $this->repository->findById(new ConversationId("id2"));
        $idFound = $found->getId();

        $this->assertEquals("id2", $found2->getId());
        $this->assertInstanceOf(Conversation::class, $found);
        $this->assertFalse($idFound->equals($found2->getId()));
    }


    public function testFindByContextId(): void
    {

        $id = new ConversationId("con_66b4d66139fd2");
        $context = new ContextId("id_modified");

        $conversation = new Conversation(new PairArray(), $context, $id,);


        $this->repository->add($conversation);
        $test = $this->repository->findById($id);
        //var_dump($test);
        /** @var Conversation */
        
        //var_dump($found);
        $this->expectException(ContextAssociatedConversationException::class);
        $this->expectExceptionMessage("The context can't be deleted, is associated at ". $id ." conversation");

        $found = $this->repository->findByContextId($context);
    }
}
