<?php

namespace Chatbot\Domain\Model\Conversation;

class PairArray
{
    /**
     * @var Pair[] $pairs
     */
    private array $pairs = [];

    public function add(Pair $pair): void
    {
        $this->pairs[] = $pair;
    }

    public function getPair(int $index): Pair
    {
        return $this->pairs[$index];
    }

    public function getNb(): int
    {
        return count($this->pairs);
    }

    public function totalToken(): int
    {
        $result = 0;
        foreach ($this->pairs as $pair) {
            $result = $result + $pair->countToken();
        }

        return $result;
    }
}
