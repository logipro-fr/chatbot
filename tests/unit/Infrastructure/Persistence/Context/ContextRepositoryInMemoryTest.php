<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Context;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use PHPUnit\Framework\TestCase;

class ContextRepositoryInMemoryTest extends TestCase
{
    private ContextRepositoryInMemory $repository;

    protected function setUp(): void
    {
        $this->repository = new ContextRepositoryInMemory();
    }

    public function testAddAndFindById(): void
    {
        $context = new Context(new ContextMessage('Test context message'));
        $contextId = $context->getContextId();

        $this->repository->add($context);

        $foundContext = $this->repository->findById($contextId);
        $this->assertEquals($context, $foundContext);
    }

    public function testFindByIdWithNonExistentId(): void
    {
        $contextId = new ContextId('non-existent-id');

        $this->expectException(\Chatbot\Infrastructure\Exception\ContextNotFoundException::class);
        $this->repository->findById($contextId);
    }

    public function testRemoveContextExistingContext(): void
    {
        $context = new Context(new ContextMessage('Test context message'));
        $this->repository->add($context);

        $this->repository->removeContext($context->getContextId());

        $this->expectException(\Chatbot\Infrastructure\Exception\ContextNotFoundException::class);
        $this->repository->findById($context->getContextId());
    }

    public function testRemoveContextNonExistentContext(): void
    {
        $contextId = new ContextId('non-existent-id');

        $this->repository->removeContext($contextId);

        $this->addToAssertionCount(1);
    }
}
