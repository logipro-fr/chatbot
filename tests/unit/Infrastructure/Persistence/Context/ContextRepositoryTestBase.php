<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Context ;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Infrastructure\Exception\ContextNotFoundException;
use Chatbot\Infrastructure\Exception\ConversationNotFoundException;
use ErrorException;
use PHPUnit\Framework\TestCase;

abstract class ContextRepositoryTestBase extends TestCase
{
    protected ContextRepositoryInterface $contextRepository;
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


        $this->contextRepository->add($context);
        $found = $this->contextRepository->findById($id);
        $this->contextRepository->add($context2);
        $found2 = $this->contextRepository->findById(new ContextId("id2"));

        $found = $this->contextRepository->findById($id);

        $found2 = $this->contextRepository->findById(new ContextId("id2"));
        $idFound = $found->getContextId();

        $this->assertEquals("id2", $found2->getContextId());
        $this->assertInstanceOf(Context::class, $found);
        $this->assertFalse($idFound->equals($found2->getContextId()));
    }

    public function testRemove(): void
    {
        $id = new ContextId("base");
        $context = new Context(new ContextMessage(""), $id);
        $this->contextRepository->add($context);
        $this->contextRepository->removeContext(($id));
        $this->expectException(ContextNotFoundException::class);
        $found = $this->contextRepository->findById($id);
    }
}
