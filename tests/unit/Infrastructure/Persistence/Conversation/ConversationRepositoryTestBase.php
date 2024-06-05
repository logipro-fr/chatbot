<?php 

namespace Chatbot\Tests\Infrastructure\Persistence\Conversation ;

use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\PairArray;
use PHPUnit\Framework\TestCase;

abstract class ConversationRepositoryTestBase extends TestCase
{
    protected ConversationRepositoryInterface $repository;
    protected function setUp(): void {
        $this->initialize();
    }

    abstract protected function initialize(): void;

    public function testFindById(): void
    {
        
        $id = new ConversationId("unId");
        $conversation = new Conversation(new PairArray(),$id);
        //var_dump($conversation);
        $conversation2 = new Conversation(new PairArray(), new ConversationId("id2"));


        $this->repository->add($conversation);
        $found = $this->repository->findById($id);
        $this->repository->add($conversation2);
        $found2 = $this->repository->findById(new ConversationId("id2"));
        $found = $this->repository->findById($id);
        $found2 = $this->repository->findById(new ConversationId("id2"));
        $idFound = $found->getId();

        $this->assertEquals("id2", $found2->getId());
        $this->assertInstanceOf(Conversation::class, $found);
        $this->assertFalse($idFound->equals($found2->getId()));
    }

}