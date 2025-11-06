<?php

namespace Chatbot\Infrastructure\Persistence\Assistant;

use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;

class AssistantRepositoryInMemory implements AssistantRepositoryInterface
{
    /** @var array<string, Assistant> */
    private array $assistants = [];

    public function add(Assistant $assistant): void
    {
        $this->assistants[$assistant->getAssistantId()->getId()] = $assistant;
    }

    public function findById(AssistantId $assistantId): ?Assistant
    {
        return $this->assistants[$assistantId->getId()] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->assistants);
    }

    public function delete(AssistantId $assistantId): void
    {
        unset($this->assistants[$assistantId->getId()]);
    }
}
