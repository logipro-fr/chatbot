<?php

namespace Chatbot\Domain\Model\Conversation;

class ConversationId
{
    public function __construct(private string $id = "")
    {
        if (empty($this->id)) {
            $this->id =  uniqid("con_");
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

    public function equals(ConversationId $conversationId): bool
    {
        if ($this->id === $conversationId->id) {
            return true;
        }
        return false;
    }
}
