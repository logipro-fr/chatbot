<?php

namespace Chatbot\Domain\Model\Conversation;


class Pair
{
    
    public function __construct(private Prompt $prompt, private Answer $answer, private PairId $pairId = new PairId())
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
}
