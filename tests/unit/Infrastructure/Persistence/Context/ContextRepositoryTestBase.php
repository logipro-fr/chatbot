<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Context ;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Conversation;
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
        $idFound = $found->getId();

        $this->assertEquals("id2", $found2->getId());
        $this->assertInstanceOf(Context::class, $found);
        $this->assertFalse($idFound->equals($found2->getId()));
    }
}
