<?php

namespace Chatbot\Domain\Model\Conversation;

class PairId
{
    public function __construct(private string $id = "")
    {
        if (empty($this->id)) {
            $this->id =  uniqid("pai_");
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

    public function equals(PairId $pairId): bool
    {
        if ($this->id === $pairId->id) {
            return true;
        }
        return false;
    }
}
