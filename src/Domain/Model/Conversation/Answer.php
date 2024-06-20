<?php

namespace Chatbot\Domain\Model\Conversation;

class Answer
{
    public function __construct(private string $message, private int $codeStatue)
    {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCodeStatus(): int
    {
        return $this->codeStatue;
    }

    public function countToken(): int
    {
        $tokenCount = new TokenCount();
        $result = $tokenCount->countToken($this->message);
        return $result;
    }

    public function equals(Answer $answer): bool
    {
        if ($this->message === $answer->message) {
            return true;
        }
        return false;
    }
}
