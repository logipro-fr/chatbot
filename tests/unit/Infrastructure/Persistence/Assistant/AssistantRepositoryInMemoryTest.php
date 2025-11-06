<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Assistant;

use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Persistence\Assistant\AssistantRepositoryInMemory;
use PHPUnit\Framework\TestCase;

class AssistantRepositoryInMemoryTest extends TestCase
{
    private AssistantRepositoryInMemory $repository;

    protected function setUp(): void
    {
        $this->repository = new AssistantRepositoryInMemory();
    }

    public function testAddAndFindById(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $assistant = new Assistant(
            $assistantId,
            'Test Assistant',
            'Test instructions',
            'external-assistant-id'
        );

        $this->repository->add($assistant);

        $foundAssistant = $this->repository->findById($assistantId);
        $this->assertEquals($assistant, $foundAssistant);
    }

    public function testFindByIdWithNonExistentId(): void
    {
        $assistantId = new AssistantId('non-existent-id');

        $result = $this->repository->findById($assistantId);
        $this->assertNull($result);
    }

    public function testFindAllWithEmptyRepository(): void
    {
        $assistants = $this->repository->findAll();
        $this->assertEmpty($assistants);
    }

    public function testFindAllWithMultipleAssistants(): void
    {
        $assistant1 = new Assistant(
            new AssistantId('assistant-1'),
            'Assistant 1',
            'Instructions 1',
            'external-1'
        );
        $assistant2 = new Assistant(
            new AssistantId('assistant-2'),
            'Assistant 2',
            'Instructions 2',
            'external-2'
        );

        $this->repository->add($assistant1);
        $this->repository->add($assistant2);

        $assistants = $this->repository->findAll();
        $this->assertCount(2, $assistants);
        $this->assertContains($assistant1, $assistants);
        $this->assertContains($assistant2, $assistants);
    }

    public function testDeleteExistingAssistant(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $assistant = new Assistant(
            $assistantId,
            'Test Assistant',
            'Test instructions',
            'external-assistant-id'
        );

        $this->repository->add($assistant);
        $this->repository->delete($assistantId);

        $result = $this->repository->findById($assistantId);
        $this->assertNull($result);
    }

    public function testDeleteNonExistentAssistant(): void
    {
        $assistantId = new AssistantId('non-existent-id');

        $this->repository->delete($assistantId);

        $this->addToAssertionCount(1);
    }
}
