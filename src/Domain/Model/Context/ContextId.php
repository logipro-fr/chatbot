<?php

namespace Chatbot\Domain\Model\Context;

class ContextId
{
    public function __construct(private string $id = "")
    {
        if (empty($this->id)) {
            $this->id =  uniqid("cot_");
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    public function equals(ContextId $contextId): bool
    {
        if ($this->id === $contextId->id) {
            return true;
        }
        return false;
    }
}