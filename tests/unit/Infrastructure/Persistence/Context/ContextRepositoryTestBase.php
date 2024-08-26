<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Context ;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Infrastructure\Exception\ConversationNotFoundException;
use ErrorException;
use PHPUnit\Framework\TestCase;

abstract class ContextRepositoryTestBase extends TestCase
{
    protected ContextRepositoryInterface $repository;
    protected function setUp(): void
    {
        $this->initialize();
    }

    abstract protected function initialize(): void;

    public function testFindById(): void
    {

        $id = new ContextId("unId");

        $context = new Context(new ContextMessage(""), $id);

        $context2 = new Context(new ContextMessage(""), new ContextId("id2"));


        $this->repository->add($context);
        $found = $this->repository->findById($id);
        $this->repository->add($context2);
        $found2 = $this->repository->findById(new ContextId("id2"));
        /** @var Conversation */
        $found = $this->repository->findById($id);
        /** @var Conversation */
        $found2 = $this->repository->findById(new ContextId("id2"));
        $idFound = $found->getConversationId();

        $this->assertEquals("id2", $found2->getConversationId());
        $this->assertInstanceOf(Context::class, $found);
        $this->assertFalse($idFound->equals($found2->getConversationId()));
    }

    public function testRemove(): void
    {
        $id = new ContextId("base");
        $context = new Context(new ContextMessage(""), $id);
        $this->repository->add($context);
        $this->repository->removeContext(($id));
        $this->expectException(ConversationNotFoundException::class);
        $found = $this->repository->findById($id);
    }
}
