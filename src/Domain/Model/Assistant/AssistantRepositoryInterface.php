<?php

namespace Chatbot\Domain\Model\Assistant;

interface AssistantRepositoryInterface
{
    public function add(Assistant $assistant): void;
    public function findById(AssistantId $assistantId): ?Assistant;
    public function findAll(): array;
    public function delete(AssistantId $assistantId): void;
}
