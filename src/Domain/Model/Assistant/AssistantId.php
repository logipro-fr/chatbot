<?php

namespace Chatbot\Domain\Model\Assistant;

class AssistantId implements \JsonSerializable
{
    private string $id;

    public function __construct(?string $id = null)
    {
        $this->id = $id ?? uniqid('assistant_', true);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function equals(AssistantId $other): bool
    {
        return $this->id === $other->id;
    }

    public function jsonSerialize(): string
    {
        return $this->id;
    }
}
