<?php

namespace Chatbot\Domain\Model\Conversation;

class Prompt
{
    public function __construct(public readonly string $prompt)
    {
    }

    public function countToken(): int
    {
        $tokenCount = new TokenCount();
        $result = $tokenCount->countToken($this->prompt);
        return $result;
    }

    public function equals(Prompt $prompt): bool
    {
        if ($this->prompt === $prompt->prompt) {
            return true;
        }
        return false;
    }

    public function getUserResquest(): string
    {
        return $this->prompt;
    }
}
