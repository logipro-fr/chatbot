<?php

namespace Chatbot\Domain\Model\Conversation;

use DateTimeImmutable;
use Safe\DateTimeImmutable as SafeDateTimeImmutable;

class Pair
{
    public function __construct(
        private Prompt $prompt, 
        private Answer $answer, 
        private PairId $pairId = new PairId(),
        private readonly DateTimeImmutable $createdAt = new SafeDateTimeImmutable(),)
    {
    }

    public function countToken(): int
    {
        $token = $this->prompt->countToken() + $this->answer->countToken();

        return $token;
    }

    public function getPrompt(): Prompt
    {
        return $this->prompt;
    }

    public function getAnswer(): Answer
    {
        return $this->answer;
    }

    public function getPairId(): PairId
    {
        return $this->pairId;
    }
}
