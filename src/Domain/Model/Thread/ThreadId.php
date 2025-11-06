<?php

namespace Chatbot\Domain\Model\Thread;

class ThreadId
{
    private string $id;

    public function __construct(?string $id = null)
    {
        $this->id = $id ?? uniqid('thr_', true);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function equals(ThreadId $other): bool
    {
        return $this->id === $other->id;
    }
}
